<?php

namespace App\Services;

use App\Models\ChallengePayment;
use App\Models\User;
use App\Services\Asaas\AsaasClient;
use App\Services\Asaas\AsaasException;
use App\Support\ChallengeAccess;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Throwable;

class ChallengePaymentService
{
    public function __construct(private readonly AsaasClient $asaas) {}

    /**
     * Garante uma cobrança utilizável para o usuário: reaproveita a pendente dentro do prazo
     * e só cria outra se não houver. O CPF só é necessário quando o cliente ainda não existe no Asaas.
     */
    public function ensureCharge(User $user, ?string $cpf, string $billingType, bool $changeBillingType = false): ChallengePayment
    {
        try {
            return Cache::lock('asaas-charge-user-'.$user->id, 30)->block(10, function () use ($user, $cpf, $billingType, $changeBillingType) {
                $user->refresh();

                $existing = $user->payments()->pending()->whereNotNull('asaas_payment_id')->latest('id')->first();

                if ($existing) {
                    if ($changeBillingType && $existing->billing_type !== $billingType) {
                        $response = $this->asaas->updatePaymentBillingType($existing->asaas_payment_id, $billingType);

                        return $this->applyStatus($existing, $response);
                    }

                    return $existing;
                }

                if ($changeBillingType) {
                    throw ValidationException::withMessages(['asaas' => 'A cobrança já não está pendente. Atualize a página para conferir o pagamento.']);
                }

                return $this->createCharge($user, $cpf, $billingType);
            });
        } catch (LockTimeoutException $e) {
            throw new AsaasException('Outra geração de cobrança está em andamento.', previous: $e);
        }
    }

    private function createCharge(User $user, ?string $cpf, string $billingType): ChallengePayment
    {
        $today = Carbon::today(ChallengeAccess::TIMEZONE);
        $lastDueDate = Carbon::parse(config('desafio.payment_last_due_date'), ChallengeAccess::TIMEZONE);

        if (ChallengeAccess::registrationIsClosed() || $today->gt($lastDueDate)) {
            throw ValidationException::withMessages(['asaas' => 'O prazo para gerar pagamentos do Desafio da Virada terminou.']);
        }

        $dueDate = $today->addDays((int) config('desafio.payment_due_days', 3))->min($lastDueDate);

        if (! $user->asaas_customer_id) {
            if (! $cpf) {
                throw new InvalidArgumentException('CPF é obrigatório para criar o cliente no Asaas.');
            }

            $customer = $this->asaas->createCustomer([
                'name' => $user->name,
                'email' => $user->email,
                'cpfCnpj' => $cpf,
                'externalReference' => (string) $user->id,
            ]);

            if (empty($customer['id'])) {
                throw new AsaasException('Resposta do Asaas sem id de cliente.');
            }

            // Salva já: se a cobrança falhar adiante, o cliente não é recriado na próxima tentativa.
            $user->forceFill(['asaas_customer_id' => $customer['id']])->save();
        }

        $payment = $user->payments()->create([
            'uuid' => (string) Str::uuid(),
            'status' => ChallengePayment::PENDING,
            'value' => config('desafio.price'),
            'due_date' => $dueDate->toDateString(),
        ]);

        try {
            $payload = [
                'customer' => $user->asaas_customer_id,
                'billingType' => $billingType,
                'value' => (float) config('desafio.price'),
                'dueDate' => $payment->due_date->format('Y-m-d'),
                'description' => 'Inscrição - Desafio da Virada',
                'externalReference' => $payment->uuid,
            ];

            if ($callback = $this->callbackUrl()) {
                $payload['callback'] = ['successUrl' => $callback, 'autoRedirect' => true];
            }

            $response = $this->asaas->createPayment($payload);

            if (empty($response['id'])) {
                throw new AsaasException('Resposta do Asaas sem id de cobrança.');
            }
        } catch (Throwable $e) {
            $payment->delete();

            throw $e;
        }

        $payment->update([
            'asaas_payment_id' => $response['id'],
            'invoice_url' => $response['invoiceUrl'] ?? null,
            'billing_type' => $response['billingType'] ?? $payload['billingType'],
            'status' => $response['status'] ?? ChallengePayment::PENDING,
        ]);

        return $payment;
    }

    /**
     * Aplica o status que veio do Asaas. Tolera eventos repetidos e fora de ordem:
     * pago não volta para pendente e reembolsado é definitivo.
     *
     * @param  array<string, mixed>  $asaasPayment
     */
    public function applyStatus(ChallengePayment $payment, array $asaasPayment): ChallengePayment
    {
        $status = (string) ($asaasPayment['status'] ?? '');

        if ($status === '') {
            return $payment;
        }

        return DB::transaction(function () use ($payment, $asaasPayment, $status) {
            $payment = ChallengePayment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status === ChallengePayment::REFUNDED) {
                return $payment;
            }

            $paid = in_array($status, ChallengePayment::PAID_STATUSES, true);

            if ($payment->isPaid() && ! $paid && $status !== ChallengePayment::REFUNDED) {
                return $payment;
            }

            $payment->fill([
                'status' => $status,
                'billing_type' => $asaasPayment['billingType'] ?? $payment->billing_type,
                'invoice_url' => $asaasPayment['invoiceUrl'] ?? $payment->invoice_url,
            ]);

            if ($paid) {
                $payment->confirmed_at ??= now();
            }

            $payment->save();

            $user = $payment->user;

            if ($paid && ! $user->challenge_paid_at) {
                $user->forceFill(['challenge_paid_at' => now()])->save();
            }

            if ($status === ChallengePayment::REFUNDED && $user->challenge_paid_at) {
                $user->forceFill(['challenge_paid_at' => null])->save();
            }

            return $payment;
        });
    }

    /** Cobrança removida no Asaas: só marca como deletada se ainda não foi paga. */
    public function markDeleted(ChallengePayment $payment): ChallengePayment
    {
        if (! $payment->isPaid() && $payment->status !== ChallengePayment::REFUNDED) {
            $payment->update(['status' => ChallengePayment::DELETED]);
        }

        return $payment;
    }

    /** Consulta o Asaas e aplica o status atual (fonte da verdade). */
    public function sync(ChallengePayment $payment): ChallengePayment
    {
        if (! $payment->asaas_payment_id) {
            return $payment;
        }

        return $this->applyStatus($payment, $this->asaas->getPayment($payment->asaas_payment_id));
    }

    /** URL de retorno do checkout. Em ambiente local o domínio não está cadastrado no Asaas, então não enviamos. */
    private function callbackUrl(): ?string
    {
        $host = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

        if ($host === '' || $host === 'localhost' || str_ends_with($host, '.test') || str_ends_with($host, '.localhost') || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        return route('pagamento.retorno');
    }
}

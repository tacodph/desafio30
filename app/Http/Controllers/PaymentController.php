<?php

namespace App\Http\Controllers;

use App\Models\ChallengePayment;
use App\Models\User;
use App\Rules\Cpf;
use App\Services\Asaas\AsaasException;
use App\Services\ChallengePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(private readonly ChallengePaymentService $payments) {}

    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAthlete()) {
            return to_route('dashboard');
        }

        if ($user->isCourtesy() || $user->hasExternalChallengePayment() || $user->hasAsaasAttestation()) {
            return to_route('dashboard');
        }

        $latest = $user->payments()->latest('id')->first();

        // O polling precisa consultar o Asaas, não só reler o status do banco.
        // Um intervalo compartilhado evita consultar a API a cada aba/recarregamento.
        if (! $user->hasPaidChallenge() && $latest?->asaas_payment_id
            && in_array($latest->status, [ChallengePayment::PENDING, ChallengePayment::OVERDUE], true)
            && Cache::add('asaas-page-sync-'.$latest->id, true, 10)) {
            try {
                $this->payments->sync($latest);
            } catch (AsaasException) {
                // Em caso de falha transitória, mantém a espera para tentar novamente.
            }
            $user->refresh();
            $latest->refresh();
        }
        return Inertia::render('Pagamento', [
            'price' => config('desafio.price'),
            'needsCpf' => ! $user->asaas_customer_id,
            // Sem invoiceUrl de propósito: o link do Asaas nunca vai para o HTML.
            'payment' => [
                'state' => $this->state($user, $latest),
                'dueDate' => $latest?->due_date?->format('d/m/Y'),
                'billingType' => $latest?->billing_type,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isAthlete(), 403);

        if ($user->hasChallengeAccess()) {
            return to_route('dashboard');
        }

        $data = $request->validate([
            'cpf' => [Rule::requiredIf(! $user->asaas_customer_id), 'nullable', 'string', new Cpf],
            'billing_type' => ['sometimes', Rule::in(['PIX', 'CREDIT_CARD'])],
            'change_billing_type' => ['sometimes', 'boolean'],
        ]);

        try {
            $this->payments->ensureCharge($user, isset($data['cpf']) ? Cpf::digits($data['cpf']) : null, $data['billing_type'] ?? 'PIX', $request->boolean('change_billing_type'));
        } catch (AsaasException $e) {
            return back()->withErrors(['asaas' => $e->userMessage()]);
        }

        return to_route('pagamento.show');
    }

    /** Leva para a fatura do Asaas. O botão "Pagar" aponta para cá, então o link visível é do nosso domínio. */
    public function pay(Request $request): RedirectResponse
    {
        if ($request->user()->hasChallengeAccess()) {
            return to_route('dashboard');
        }

        $payment = $request->user()->payments()->pending()->whereNotNull('invoice_url')->latest('id')->first();

        if (! $payment) {
            return to_route('pagamento.show');
        }

        return redirect()->away($payment->invoice_url);
    }

    /** Volta do checkout: confere o status no Asaas uma vez e segue para a tela certa. */
    public function callback(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->isCourtesy() || $user->hasExternalChallengePayment() || $user->hasAsaasAttestation()) {
            return to_route('dashboard');
        }
        $payment = $user->payments()->latest('id')->first();

        if ($payment) {
            try {
                $this->payments->sync($payment);
            } catch (AsaasException) {
                // O webhook e a reconciliação resolvem; a tela segue em "aguardando".
            }
        }

        return to_route('pagamento.show');
    }

    private function state(User $user, ?ChallengePayment $latest): string
    {
        if ($user->hasPaidChallenge()) {
            return 'confirmado';
        }

        if (! $latest) {
            return 'novo';
        }

        if ($latest->status === ChallengePayment::REFUNDED) {
            return 'reembolsado';
        }

        if ($latest->status === ChallengePayment::PENDING && $latest->asaas_payment_id && $latest->due_date->gte(today())) {
            return 'pendente';
        }

        return 'vencido';
    }
}

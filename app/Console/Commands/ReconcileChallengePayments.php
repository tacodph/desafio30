<?php

namespace App\Console\Commands;

use App\Models\ChallengePayment;
use App\Services\Asaas\AsaasException;
use App\Services\ChallengePaymentService;
use Illuminate\Console\Command;

class ReconcileChallengePayments extends Command
{
    protected $signature = 'desafio:reconcile-payments {--all : Confere também cobranças já recebidas com forma definida}';

    protected $description = 'Atualiza no Asaas cobranças pendentes, vencidas, confirmadas e com forma de pagamento indefinida';

    public function handle(ChallengePaymentService $service): int
    {
        $payments = ChallengePayment::query()
            ->whereNotNull('asaas_payment_id')
            ->whereNotIn('status', [ChallengePayment::REFUNDED, ChallengePayment::DELETED]);

        if (! $this->option('all')) {
            $payments->where(function ($query) {
                $query->whereIn('status', [ChallengePayment::PENDING, ChallengePayment::OVERDUE, ChallengePayment::CONFIRMED])
                    ->orWhereNull('billing_type')
                    ->orWhere('billing_type', 'UNDEFINED');
            });
        }

        $checked = 0;
        $failed = 0;

        $payments->chunkById(100, function ($payments) use ($service, &$checked, &$failed) {
            foreach ($payments as $payment) {
                $checked++;

                try {
                    $service->sync($payment);
                } catch (AsaasException $exception) {
                    $failed++;
                    $this->warn('Falha na cobrança '.$payment->asaas_payment_id.': '.$exception->getMessage());
                }
            }
        });

        $this->info("Conferidas: {$checked} | falhas: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\AsaasSyncRun;
use App\Models\ChallengePayment;
use App\Services\Asaas\AsaasException;
use App\Services\ChallengePaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ReconcileChallengePayments extends Command
{
    protected $signature = 'desafio:reconcile-payments {--all : Confere também cobranças já recebidas com forma definida} {--scheduled : Identifica uma execução agendada} {--run-id= : Identifica uma execução solicitada pelo painel}';

    protected $description = 'Atualiza no Asaas cobranças pendentes, vencidas, confirmadas e com forma de pagamento indefinida';

    public function handle(ChallengePaymentService $service): int
    {
        $runId = $this->option('run-id');
        $run = $runId ? AsaasSyncRun::query()->where('source', 'manual')->where('status', 'queued')->find($runId) : null;
        if ($runId && ! $run) {
            return self::SUCCESS;
        }

        $result = Cache::lock('asaas-reconcile-payments', 900)->get(function () use ($service, $run) {
            $run ??= AsaasSyncRun::create(['source' => $this->option('scheduled') ? 'scheduled' : 'console', 'status' => 'running']);
            $run->update(['status' => 'running', 'started_at' => now()]);

            try {
                return $this->reconcile($service, $run);
            } catch (Throwable $error) {
                $run->update(['status' => 'failed', 'finished_at' => now()]);

                throw $error;
            }
        });

        if ($result === false) {
            $run?->update(['status' => 'skipped', 'finished_at' => now()]);
            $this->warn('Já existe uma sincronização com o Asaas em andamento.');

            return self::SUCCESS;
        }

        return $result;
    }

    private function reconcile(ChallengePaymentService $service, AsaasSyncRun $run): int
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
        $run->update([
            'checked' => $checked,
            'failed' => $failed,
            'status' => $failed === 0 ? 'completed' : ($failed === $checked ? 'failed' : 'partial'),
            'finished_at' => now(),
        ]);

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

<?php

namespace App\Jobs;

use App\Models\AsaasSyncRun;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

class ReconcileAsaasPayments implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 75;

    public bool $failOnTimeout = true;

    public function __construct(public int $runId) {}

    public function handle(): void
    {
        Artisan::call('desafio:reconcile-payments', ['--run-id' => $this->runId]);
    }

    public function failed(): void
    {
        AsaasSyncRun::query()->whereKey($this->runId)->whereIn('status', ['queued', 'running'])
            ->update(['status' => 'failed', 'finished_at' => now()]);
    }
}

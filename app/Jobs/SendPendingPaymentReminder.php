<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\EmailLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class SendPendingPaymentReminder implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        public int $athleteId,
        public string $subject,
        public string $body,
        public ?int $emailLogId = null,
    ) {}

    public function handle(): void
    {
        $athlete = User::query()->withPendingChallengePayment()->find($this->athleteId);

        $log = isset($this->emailLogId) ? EmailLog::find($this->emailLogId) : null;
        if ($log && in_array($log->status, ['sent', 'logged', 'processed', 'skipped'], true)) {
            return;
        }

        if (! $athlete) {
            $log?->update(['status' => 'skipped', 'error' => 'O atleta não possui mais pagamento pendente.']);
            return;
        }

        Mail::raw($this->body, function (Message $message) use ($athlete, $log) {
            $message->to($athlete->email)->subject($this->subject);
            $message->getHeaders()->addTextHeader('X-Desafio-Category', 'payment_reminder');
            if ($log) {
                $message->getHeaders()->addTextHeader('X-Desafio-Email-Log', (string) $log->id);
            }
        });
    }

    public function failed(?\Throwable $exception): void
    {
        if (isset($this->emailLogId)) {
            EmailLog::whereKey($this->emailLogId)->whereIn('status', ['queued', 'sending'])->update([
                'status' => 'failed', 'error' => 'O processamento do lembrete falhou. Consulte o registro de falhas da fila.',
            ]);
        }
    }
}

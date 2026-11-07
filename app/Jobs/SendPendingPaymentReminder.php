<?php

namespace App\Jobs;

use App\Models\User;
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
    ) {}

    public function handle(): void
    {
        $athlete = User::query()->withPendingChallengePayment()->find($this->athleteId);

        if ($athlete) {
            Mail::raw($this->body, fn (Message $message) => $message->to($athlete->email)->subject($this->subject));
        }
    }
}

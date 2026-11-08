<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Jobs\SendPendingPaymentReminder;
use App\Models\ChallengePayment;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;
use Tests\TestCase;

class EmailLogTest extends TestCase
{
    use RefreshDatabase;

    private function transport(bool $fail = false): void
    {
        config(['mail.default' => 'audit_test', 'mail.mailers.audit_test' => ['transport' => 'audit_test']]);
        Mail::extend('audit_test', fn () => new class($fail) implements TransportInterface
        {
            public function __construct(private bool $fail) {}

            public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
            {
                if ($this->fail) {
                    throw new TransportException('SMTP indisponível para teste.');
                }

                return new SentMessage($message, $envelope ?? Envelope::create($message));
            }

            public function __toString(): string { return 'audit-test'; }
        });
    }

    private function pendingAthlete(): User
    {
        $athlete = User::factory()->unpaid()->create();
        ChallengePayment::create(['user_id' => $athlete->id, 'asaas_payment_id' => 'pay_'.$athlete->id, 'status' => ChallengePayment::PENDING, 'value' => 79.90, 'due_date' => today()->addDay()]);

        return $athlete;
    }

    public function test_transport_acceptance_records_message_content_and_recipients(): void
    {
        $this->transport();
        Mail::raw('Mensagem enviada.', fn ($message) => $message->to('atleta@example.com')->replyTo('support@example.com')->subject('Assunto de teste'));
        $log = EmailLog::sole();
        $this->assertSame('sent', $log->status);
        $this->assertSame('Mensagem enviada.', $log->body_text);
        $this->assertSame('atleta@example.com', $log->recipient_email);
        $this->assertSame('support@example.com', $log->reply_to[0]['email']);
        $this->assertNotNull($log->sent_at);
        $this->assertSame(1, $log->attempts);
    }

    public function test_transport_failure_is_saved_and_is_not_reported_as_sent(): void
    {
        $this->transport(true);
        try {
            Mail::raw('Mensagem', fn ($message) => $message->to('atleta@example.com')->subject('Falha'));
            $this->fail('Era esperada uma falha no envio.');
        } catch (TransportException $exception) {
            $this->assertSame('SMTP indisponível para teste.', $exception->getMessage());
        }
        $log = EmailLog::sole();
        $this->assertSame('failed', $log->status);
        $this->assertNull($log->sent_at);
        $this->assertStringContainsString('SMTP indisponível', $log->error);
    }

    public function test_array_transport_is_labeled_as_test_not_delivered(): void
    {
        config(['mail.default' => 'array']);
        Mail::raw('Teste', fn ($message) => $message->to('atleta@example.com')->subject('Teste'));
        $this->assertSame('logged', EmailLog::sole()->status);
        $this->assertNull(EmailLog::sole()->sent_at);
    }

    public function test_reminder_is_recorded_before_queue_processing_and_reuses_the_record(): void
    {
        Bus::fake();
        $athlete = $this->pendingAthlete();
        $admin = User::factory()->ofRole(UserRole::Administrador)->create();
        $this->actingAs($admin)->post(route('admin.payment-reminders.store'), ['athlete_ids' => [$athlete->id], 'subject' => 'Lembrete', 'body' => 'Seu pagamento está pendente.'])->assertRedirect();
        $log = EmailLog::sole();
        $this->assertSame('queued', $log->status);
        $this->assertSame($admin->id, $log->actor_id);
        Bus::assertDispatched(SendPendingPaymentReminder::class, fn ($job) => $job->emailLogId === $log->id);
        $this->transport();
        $job = new SendPendingPaymentReminder($athlete->id, $log->subject, $log->body_text, $log->id);
        $job->handle();
        $job->handle();
        $this->assertSame('sent', $log->fresh()->status);
        $this->assertSame(1, $log->fresh()->attempts);
        $this->assertDatabaseCount('email_logs', 1);
    }

    public function test_paid_athlete_reminder_is_skipped(): void
    {
        $athlete = User::factory()->create();
        $log = EmailLog::create(['category' => 'payment_reminder', 'recipient_email' => $athlete->email, 'subject' => 'Lembrete', 'body_text' => 'Mensagem', 'status' => 'queued']);
        (new SendPendingPaymentReminder($athlete->id, 'Lembrete', 'Mensagem', $log->id))->handle();
        $this->assertSame('skipped', $log->fresh()->status);
        $this->assertSame(0, $log->fresh()->attempts);
    }

    public function test_password_reset_email_is_logged_without_exposing_the_token(): void
    {
        config(['mail.default' => 'array']);
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHasNoErrors();
        $log = EmailLog::sole();
        $this->assertSame('notification', $log->category);
        $this->assertNull($log->body_html);
        $this->assertStringContainsString('omitido', $log->body_text);
        $this->assertStringNotContainsString('reset-password', $log->body_text);
    }

    public function test_history_is_admin_only_and_filters_by_recipient(): void
    {
        EmailLog::create(['recipient_email' => 'ana@example.com', 'subject' => 'Lembrete', 'body_text' => 'Texto', 'status' => 'queued']);
        EmailLog::create(['recipient_email' => 'bia@example.com', 'subject' => 'Outro', 'body_text' => 'Texto', 'status' => 'failed']);
        $this->actingAs(User::factory()->create())->get(route('admin.email-logs'))->assertForbidden();
        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->create())
            ->get(route('admin.email-logs', ['search' => 'ana@example.com']))
            ->assertInertia(fn (Assert $page) => $page->component('dashboard/EmailLogs')->has('logs.data', 1)->where('logs.data.0.recipient_email', 'ana@example.com'));
    }
}

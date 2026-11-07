<?php

namespace Tests\Feature;

use App\Jobs\ProcessAsaasWebhook;
use App\Models\AsaasWebhookEvent;
use App\Models\ChallengePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AsaasWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = str_repeat('a1b2', 10);

        config([
            'services.asaas.api_key' => 'chave-de-teste',
            'services.asaas.webhook_token' => $this->token,
            'services.asaas.base_url' => 'https://api-sandbox.asaas.com/v3',
        ]);
    }

    private function payment(User $user, string $status = 'PENDING'): ChallengePayment
    {
        return ChallengePayment::create([
            'user_id' => $user->id,
            'asaas_payment_id' => 'pay_123',
            'status' => $status,
            'value' => 79.90,
            'due_date' => today()->addDays(2),
        ]);
    }

    private function send(string $event, string $status = 'RECEIVED', string $eventId = 'evt_1', ?string $token = null)
    {
        return $this->postJson('/webhooks/asaas', [
            'id' => $eventId,
            'event' => $event,
            'payment' => ['id' => 'pay_123', 'status' => $status],
        ], ['asaas-access-token' => $token ?? $this->token]);
    }

    public function test_invalid_token_is_rejected(): void
    {
        Queue::fake();

        $this->send('PAYMENT_RECEIVED', token: 'errado')->assertStatus(401);
        $this->postJson('/webhooks/asaas', ['event' => 'PAYMENT_RECEIVED'])->assertStatus(401);

        Queue::assertNothingPushed();
        $this->assertSame(0, AsaasWebhookEvent::query()->count());
    }

    public function test_unconfigured_token_never_accepts_requests(): void
    {
        config(['services.asaas.webhook_token' => null]);

        $this->postJson('/webhooks/asaas', ['event' => 'PAYMENT_RECEIVED'])->assertStatus(401);
    }

    public function test_unhandled_events_are_acknowledged_and_ignored(): void
    {
        Queue::fake();

        $this->send('PAYMENT_CREATED', 'PENDING')->assertOk();

        Queue::assertNothingPushed();
        $this->assertSame(0, AsaasWebhookEvent::query()->count());
    }

    public function test_received_event_unlocks_access_after_checking_the_api(): void
    {
        Http::fake(['*/payments/pay_123' => Http::response(['id' => 'pay_123', 'status' => 'RECEIVED', 'billingType' => 'PIX'], 200)]);
        $user = User::factory()->unpaid()->create();
        $this->payment($user);

        $this->send('PAYMENT_RECEIVED')->assertOk();

        $user->refresh();
        $this->assertTrue($user->hasPaidChallenge());
        $this->assertSame('RECEIVED', ChallengePayment::query()->first()->status);
        $this->assertNotNull(AsaasWebhookEvent::query()->first()->processed_at);
    }

    public function test_status_comes_from_the_api_not_from_the_webhook_body(): void
    {
        Http::fake(['*/payments/pay_123' => Http::response(['id' => 'pay_123', 'status' => 'PENDING'], 200)]);
        $user = User::factory()->unpaid()->create();
        $this->payment($user);

        $this->send('PAYMENT_RECEIVED', 'RECEIVED')->assertOk();

        $this->assertFalse($user->fresh()->hasPaidChallenge());
    }

    public function test_updated_event_refreshes_billing_type_without_confirming_an_unpaid_charge(): void
    {
        Http::fake(['*/payments/pay_123' => Http::response(['id' => 'pay_123', 'status' => 'PENDING', 'billingType' => 'PIX'], 200)]);
        $user = User::factory()->unpaid()->create();
        $payment = $this->payment($user);
        $payment->update(['billing_type' => 'UNDEFINED']);

        $this->send('PAYMENT_UPDATED', 'PENDING')->assertOk();

        $this->assertSame('PIX', $payment->fresh()->billing_type);
        $this->assertSame('PENDING', $payment->fresh()->status);
        $this->assertFalse($user->fresh()->hasPaidChallenge());
        $this->assertNotNull(AsaasWebhookEvent::query()->first()->processed_at);
    }

    public function test_repeated_event_does_not_repeat_effects(): void
    {
        Http::fake(['*/payments/pay_123' => Http::response(['id' => 'pay_123', 'status' => 'RECEIVED'], 200)]);
        $user = User::factory()->unpaid()->create();
        $this->payment($user);

        $this->send('PAYMENT_RECEIVED')->assertOk();
        $firstPaidAt = $user->fresh()->challenge_paid_at;

        $this->travel(5)->minutes();
        $this->send('PAYMENT_RECEIVED')->assertOk();

        $this->assertSame(1, AsaasWebhookEvent::query()->count());
        $this->assertEquals($firstPaidAt, $user->fresh()->challenge_paid_at);
        Http::assertSentCount(1);
    }

    public function test_confirmed_after_received_does_not_regress_and_pending_never_overrides_paid(): void
    {
        Http::fake(['*/payments/pay_123' => Http::sequence()
            ->push(['id' => 'pay_123', 'status' => 'RECEIVED'], 200)
            ->push(['id' => 'pay_123', 'status' => 'CONFIRMED'], 200)
            ->push(['id' => 'pay_123', 'status' => 'PENDING'], 200)]);
        $user = User::factory()->unpaid()->create();
        $this->payment($user);

        $this->send('PAYMENT_RECEIVED', eventId: 'evt_1');
        $this->send('PAYMENT_CONFIRMED', eventId: 'evt_2');
        $this->send('PAYMENT_OVERDUE', eventId: 'evt_3');

        $this->assertTrue($user->fresh()->hasPaidChallenge());
        $this->assertContains(ChallengePayment::query()->first()->status, ['RECEIVED', 'CONFIRMED']);
    }

    public function test_refund_blocks_access_again(): void
    {
        Http::fake(['*/payments/pay_123' => Http::response(['id' => 'pay_123', 'status' => 'REFUNDED'], 200)]);
        $user = User::factory()->create();
        $this->payment($user, 'RECEIVED');

        $this->send('PAYMENT_REFUNDED', 'REFUNDED')->assertOk();

        $this->assertFalse($user->fresh()->hasPaidChallenge());
        $this->assertSame('REFUNDED', ChallengePayment::query()->first()->status);
    }

    public function test_deleted_event_marks_unpaid_charge_as_deleted(): void
    {
        Http::fake();
        $user = User::factory()->unpaid()->create();
        $this->payment($user);

        $this->send('PAYMENT_DELETED', 'PENDING')->assertOk();

        $this->assertSame('DELETED', ChallengePayment::query()->first()->status);
        Http::assertNothingSent();
    }

    public function test_unknown_charge_is_acknowledged(): void
    {
        Http::fake();

        $this->send('PAYMENT_RECEIVED')->assertOk();

        $this->assertNotNull(AsaasWebhookEvent::query()->first()->processed_at);
    }

    public function test_valid_event_is_dispatched_to_the_queue(): void
    {
        Queue::fake();

        $this->send('PAYMENT_RECEIVED')->assertOk();

        Queue::assertPushed(ProcessAsaasWebhook::class);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Jobs\ReconcileAsaasPayments;
use App\Models\AsaasSyncRun;
use App\Models\ChallengePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReconcileChallengePaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.asaas.api_key' => 'test-key', 'services.asaas.base_url' => 'https://api-sandbox.asaas.com/v3']);
    }

    private function payment(string $id, string $status, ?string $billingType): ChallengePayment
    {
        return ChallengePayment::create([
            'user_id' => User::factory()->unpaid()->create()->id,
            'asaas_payment_id' => $id,
            'status' => $status,
            'billing_type' => $billingType,
            'value' => 79.90,
            'due_date' => today()->subDays(40),
            'created_at' => now()->subDays(60),
        ]);
    }

    public function test_admin_can_request_sync_and_see_its_result_in_the_panel(): void
    {
        Queue::fake();
        Http::fake(['*/payments/*' => Http::response(['status' => 'RECEIVED', 'billingType' => 'PIX'])]);
        $payment = $this->payment('pay_manual', 'PENDING', 'PIX');

        $this->actingAs(User::factory()->ofRole(UserRole::Nutricionista)->create())
            ->post(route('admin.payments.sync'))->assertForbidden();
        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->create());
        $this->from(route('admin.payments'))->post(route('admin.payments.sync'))->assertRedirect(route('admin.payments'));
        $this->post(route('admin.payments.sync'))->assertRedirect();
        Queue::assertPushed(ReconcileAsaasPayments::class, 1);

        $run = AsaasSyncRun::query()->sole();
        $this->assertSame('queued', $run->status);
        (new ReconcileAsaasPayments($run->id))->handle();

        $this->assertSame('RECEIVED', $payment->fresh()->status);
        $this->get(route('admin.payments'))->assertInertia(fn (Assert $page) => $page
            ->where('asaasSync.latest.status', 'completed')
            ->where('asaasSync.latest.checked', 1)
            ->where('asaasSync.latest.failed', 0)
            ->where('asaasSync.active', false)
            ->where('asaasSync.lastSuccessful', fn ($value) => is_string($value) && $value !== ''));
    }

    public function test_reconciliation_recovers_old_overdue_confirmed_and_undefined_payments(): void
    {
        Http::fake(['*/payments/*' => Http::response(['status' => 'RECEIVED', 'billingType' => 'PIX'], 200)]);
        $payments = [
            $this->payment('pay_old', 'PENDING', 'UNDEFINED'),
            $this->payment('pay_overdue', 'OVERDUE', 'BOLETO'),
            $this->payment('pay_confirmed', 'CONFIRMED', 'CREDIT_CARD'),
            $this->payment('pay_unknown', 'RECEIVED', null),
        ];
        // created_at is not mass assignable; make the old-charge case explicit.
        $payments[0]->forceFill(['created_at' => now()->subDays(60)])->save();
        $this->payment('pay_received', 'RECEIVED', 'PIX');
        $this->payment('pay_refunded', 'REFUNDED', null);
        $this->payment('pay_deleted', 'DELETED', null);

        $this->artisan('desafio:reconcile-payments')->expectsOutput('Conferidas: 4 | falhas: 0')->assertSuccessful();

        foreach ($payments as $payment) {
            $this->assertSame('RECEIVED', $payment->fresh()->status);
            $this->assertSame('PIX', $payment->fresh()->billing_type);
            $this->assertTrue($payment->user->fresh()->hasPaidChallenge());
        }
        Http::assertSentCount(4);
    }

    public function test_full_reconciliation_also_repairs_a_received_payment_with_the_wrong_billing_type(): void
    {
        Http::fake(['*/payments/pay_received' => Http::response(['status' => 'RECEIVED', 'billingType' => 'PIX'], 200)]);
        $payment = $this->payment('pay_received', 'RECEIVED', 'BOLETO');

        $this->artisan('desafio:reconcile-payments', ['--all' => true])->assertSuccessful();

        $this->assertSame('PIX', $payment->fresh()->billing_type);
        Http::assertSentCount(1);
    }

    public function test_api_failure_does_not_confirm_payment_and_is_reported_as_a_failure(): void
    {
        Http::fake(['*/payments/*' => Http::response([], 503)]);
        $payment = $this->payment('pay_failed', 'PENDING', 'UNDEFINED');

        $this->artisan('desafio:reconcile-payments')->assertFailed();

        $this->assertSame('PENDING', $payment->fresh()->status);
        $this->assertSame('UNDEFINED', $payment->fresh()->billing_type);
        $this->assertFalse($payment->user->fresh()->hasPaidChallenge());
    }
}

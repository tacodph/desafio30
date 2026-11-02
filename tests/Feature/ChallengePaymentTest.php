<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ChallengePayment;
use App\Models\User;
use App\Rules\Cpf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChallengePaymentTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_CPF = '529.982.247-25';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.asaas.api_key' => 'chave-de-teste',
            'services.asaas.base_url' => 'https://api-sandbox.asaas.com/v3',
            'desafio.price' => 79.90,
        ]);
    }

    private function fakeAsaas(string $status = 'PENDING'): void
    {
        Http::fake([
            '*/customers' => Http::response(['id' => 'cus_123'], 200),
            '*/payments' => Http::response([
                'id' => 'pay_123',
                'status' => $status,
                'invoiceUrl' => 'https://sandbox.asaas.com/i/abc123',
            ], 200),
        ]);
    }

    public function test_cpf_rule_validates_check_digits(): void
    {
        $this->assertTrue(Cpf::isValid(self::VALID_CPF));
        $this->assertTrue(Cpf::isValid('52998224725'));
        $this->assertFalse(Cpf::isValid('529.982.247-24'));
        $this->assertFalse(Cpf::isValid('111.111.111-11'));
        $this->assertFalse(Cpf::isValid('123'));
    }

    public function test_first_access_creates_customer_and_charge_with_fixed_price(): void
    {
        $this->fakeAsaas();
        $user = User::factory()->unpaid()->create();

        $this->actingAs($user)
            ->post(route('pagamento.store'), ['cpf' => self::VALID_CPF, 'value' => 1])
            ->assertRedirect(route('pagamento.show', absolute: false));

        Http::assertSent(function (Request $request) {
            if (! str_ends_with($request->url(), '/payments')) {
                return false;
            }

            return $request['billingType'] === 'UNDEFINED'
                && (float) $request['value'] === 79.90
                && $request['customer'] === 'cus_123'
                && $request['description'] === 'Inscrição - Desafio 30 Firme'
                && $request->hasHeader('access_token', 'chave-de-teste');
        });

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/customers') && $request['cpfCnpj'] === '52998224725');

        $payment = ChallengePayment::query()->firstOrFail();
        $this->assertSame('pay_123', $payment->asaas_payment_id);
        $this->assertSame('79.90', $payment->value);
        $this->assertSame('cus_123', $user->fresh()->asaas_customer_id);
    }

    public function test_second_access_and_double_click_do_not_create_another_charge(): void
    {
        $this->fakeAsaas();
        $user = User::factory()->unpaid()->create();

        $this->actingAs($user)->post(route('pagamento.store'), ['cpf' => self::VALID_CPF]);
        $this->actingAs($user)->post(route('pagamento.store'), ['cpf' => self::VALID_CPF]);

        $this->assertSame(1, ChallengePayment::query()->count());
        Http::assertSentCount(2); // 1 cliente + 1 cobrança
    }

    public function test_expired_charge_generates_a_new_one_reusing_the_customer(): void
    {
        $this->fakeAsaas();
        $user = User::factory()->unpaid()->create(['asaas_customer_id' => 'cus_123']);
        ChallengePayment::create([
            'user_id' => $user->id,
            'asaas_payment_id' => 'pay_old',
            'status' => 'PENDING',
            'value' => 79.90,
            'due_date' => today()->subDay(),
        ]);

        $this->actingAs($user)->post(route('pagamento.store'))->assertSessionHasNoErrors();

        $this->assertSame(2, ChallengePayment::query()->count());
        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/customers'));
    }

    public function test_invalid_cpf_is_rejected(): void
    {
        Http::fake();
        $user = User::factory()->unpaid()->create();

        $this->actingAs($user)
            ->post(route('pagamento.store'), ['cpf' => '111.111.111-11'])
            ->assertSessionHasErrors('cpf');

        Http::assertNothingSent();
    }

    public function test_asaas_failure_shows_friendly_error_and_leaves_no_charge(): void
    {
        Http::fake([
            '*/customers' => Http::response(['id' => 'cus_123'], 200),
            '*/payments' => Http::response(['errors' => [['code' => 'x', 'description' => 'detalhe técnico']]], 400),
        ]);
        $user = User::factory()->unpaid()->create();

        $this->actingAs($user)
            ->post(route('pagamento.store'), ['cpf' => self::VALID_CPF])
            ->assertSessionHasErrors('asaas');

        $this->assertSame(0, ChallengePayment::query()->count());
        $this->assertSame('cus_123', $user->fresh()->asaas_customer_id);
    }

    public function test_page_never_exposes_the_asaas_invoice_url(): void
    {
        $this->fakeAsaas();
        $user = User::factory()->unpaid()->create();
        $this->actingAs($user)->post(route('pagamento.store'), ['cpf' => self::VALID_CPF]);

        $response = $this->actingAs($user)->get(route('pagamento.show'));

        $response->assertOk();
        $this->assertStringNotContainsString('sandbox.asaas.com', $response->getContent());
        $this->assertStringNotContainsString('abc123', $response->getContent());
    }

    public function test_pay_redirects_to_the_invoice_of_the_owner_only(): void
    {
        $this->fakeAsaas();
        $user = User::factory()->unpaid()->create();
        $this->actingAs($user)->post(route('pagamento.store'), ['cpf' => self::VALID_CPF]);

        $this->actingAs($user)->get(route('pagamento.pagar'))->assertRedirect('https://sandbox.asaas.com/i/abc123');

        $other = User::factory()->unpaid()->create();
        $this->actingAs($other)->get(route('pagamento.pagar'))->assertRedirect(route('pagamento.show', absolute: false));
    }

    public function test_unpaid_athlete_is_redirected_to_payment(): void
    {
        $user = User::factory()->unpaid()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('pagamento.show', absolute: false));
        $this->actingAs($user)->get(route('athlete.start.first-day'))->assertRedirect(route('pagamento.show', absolute: false));
        $this->actingAs($user)->get(route('pagamento.show'))->assertOk();
    }

    public function test_paid_athlete_and_staff_pass_the_gate(): void
    {
        $paid = User::factory()->create();
        $this->actingAs($paid)->get(route('dashboard'))->assertStatus(302)->assertRedirectContains('/atleta/');

        foreach ([UserRole::Administrador, UserRole::Nutricionista, UserRole::Coach, UserRole::Marketing] as $role) {
            $staff = User::factory()->ofRole($role)->unpaid()->create();
            $this->actingAs($staff)->get(route('dashboard'))->assertOk();
        }
    }

    public function test_return_from_checkout_syncs_status_and_unlocks_access(): void
    {
        Http::fake(['*/payments/pay_123' => Http::response(['id' => 'pay_123', 'status' => 'RECEIVED', 'billingType' => 'PIX'], 200)]);
        $user = User::factory()->unpaid()->create(['asaas_customer_id' => 'cus_123']);
        ChallengePayment::create([
            'user_id' => $user->id,
            'asaas_payment_id' => 'pay_123',
            'status' => 'PENDING',
            'value' => 79.90,
            'due_date' => today()->addDays(2),
        ]);

        $this->actingAs($user)->get(route('pagamento.retorno'))->assertRedirect(route('pagamento.show', absolute: false));

        $this->assertTrue($user->fresh()->hasPaidChallenge());
        $this->assertSame('PIX', ChallengePayment::query()->first()->billing_type);
    }

    public function test_reconcile_command_confirms_pending_payments(): void
    {
        Http::fake(['*/payments/pay_123' => Http::response(['id' => 'pay_123', 'status' => 'CONFIRMED'], 200)]);
        config(['services.asaas.api_key' => 'chave-de-teste']);
        $user = User::factory()->unpaid()->create();
        $payment = ChallengePayment::create([
            'user_id' => $user->id,
            'asaas_payment_id' => 'pay_123',
            'status' => 'PENDING',
            'value' => 79.90,
            'due_date' => today()->addDays(2),
        ]);
        $payment->forceFill(['created_at' => now()->subMinutes(30)])->save();

        $this->artisan('desafio:reconcile-payments')->assertSuccessful();

        $this->assertTrue($user->fresh()->hasPaidChallenge());
    }
}

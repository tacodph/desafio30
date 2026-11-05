<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AthleteProfile;
use App\Models\ChallengePayment;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/admin/usuarios')->assertRedirect('/login');
        $this->get('/admin/usuarios/novo')->assertRedirect('/login');
        $this->get('/equipe/atletas')->assertRedirect('/login');
        $this->get('/atleta/evolucao/dias')->assertRedirect('/login');
        $this->get('/pontuacao')->assertRedirect('/login');
        $this->get('/atleta/ponto-de-partida/como-tirar-medidas')->assertRedirect('/login');
    }

    public function test_a_logged_in_user_can_open_the_scoring_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/pontuacao')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Pontuacao')
                ->where('canEdit', false)
                ->has('daily', 6)
                ->has('bonus', 3));
    }

    public function test_only_the_admin_can_edit_scoring(): void
    {
        $admin = User::factory()->ofRole(UserRole::Administrador)->create();
        $athlete = User::factory()->create();

        $this->actingAs($admin)
            ->get('/pontuacao')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('canEdit', true));

        $daily = \App\Models\ScoreRule::query()->where('group', 'daily')->orderBy('sort')->get(['id', 'label', 'points'])->map->only(['id', 'label', 'points'])->all();
        $bonus = \App\Models\ScoreRule::query()->where('group', 'bonus')->orderBy('sort')->get(['id', 'label', 'points'])->map->only(['id', 'label', 'points'])->all();
        $daily[1]['points'] = 5;
        $daily[1]['label'] = 'Treino pesado';

        $this->actingAs($athlete)
            ->put('/pontuacao', ['daily' => $daily, 'bonus' => $bonus])
            ->assertForbidden();

        $this->actingAs($admin)
            ->put('/pontuacao', ['daily' => $daily, 'bonus' => $bonus])
            ->assertRedirect('/pontuacao');

        $this->assertDatabaseHas('score_rules', ['key' => 'movement', 'label' => 'Treino pesado', 'points' => 5]);

        $this->actingAs($athlete)
            ->get('/pontuacao')
            ->assertInertia(fn (Assert $page) => $page->where('daily.1.label', 'Treino pesado')->where('daily.1.points', 5)->where('dailyMax', 12));
    }

    public function test_a_logged_in_user_can_open_the_measures_guide(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/atleta/ponto-de-partida/como-tirar-medidas')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('dashboard/ComoTirarMedidas'));
    }

    public function test_each_role_sees_its_own_dashboard(): void
    {
        $athlete = User::factory()->create();
        AthleteProfile::query()->create(['user_id' => $athlete->id, 'welcomed_at' => now(), 'goals_set_at' => now()]);

        $this->actingAs($athlete)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('dashboard/Atleta'));

        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('dashboard/Admin')->has('counts', 5));

        $this->actingAs(User::factory()->ofRole(UserRole::Coach)->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('dashboard/Equipe'));

        $this->actingAs(User::factory()->ofRole(UserRole::Nutricionista)->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('dashboard/Equipe'));

        $this->actingAs(User::factory()->ofRole(UserRole::Marketing)->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('dashboard/Equipe'));
    }

    public function test_payment_overview_counts_charges_and_unique_customers_for_authorized_staff(): void
    {
        $athlete = User::factory()->create();
        $today = today('America/Sao_Paulo');

        foreach ([ChallengePayment::RECEIVED, ChallengePayment::RECEIVED, ChallengePayment::PENDING] as $index => $status) {
            ChallengePayment::create([
                'user_id' => $athlete->id,
                'status' => $status,
                'value' => 79.90,
                'due_date' => $today->copy()->subDay(),
                'confirmed_at' => $today->copy()->addMinutes($index === 0 ? 30 : -1)->utc(),
            ]);
        }

        foreach ([UserRole::Administrador, UserRole::Nutricionista] as $role) {
            $this->actingAs(User::factory()->ofRole($role)->create())
                ->get('/dashboard')
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->has('dailyPayments', 1)
                    ->where('dailyPayments.0.athlete', $athlete->name)
                    ->where('dailyPayments.0.time', '00:30')
                    ->where('dailyPayments.0.amount', 79.9)
                    ->has('paymentOverview', 4)
                    ->where('paymentOverview.0', ['status' => 'RECEIVED', 'quantity' => 2, 'customers' => 1, 'amount' => 159.8])
                    ->where('paymentOverview.2.quantity', 0)
                    ->where('paymentOverview.3', ['status' => 'OVERDUE', 'quantity' => 1, 'customers' => 1, 'amount' => 79.9]));
        }

        $this->actingAs(User::factory()->ofRole(UserRole::Coach)->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('paymentOverview', null)->where('dailyPayments', null));
    }

    public function test_only_the_admin_can_open_the_user_list(): void
    {
        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->create())
            ->get('/admin/usuarios')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('dashboard/Users'));

        $this->actingAs(User::factory()->create())
            ->get('/admin/usuarios')
            ->assertForbidden();

        $this->actingAs(User::factory()->ofRole(UserRole::Coach)->create())
            ->get('/admin/usuarios')
            ->assertForbidden();
    }

    public function test_admin_and_nutritionist_can_view_payment_requests_but_other_roles_cannot(): void
    {
        config([
            'services.asaas.base_url' => 'https://asaas.example/v3',
            'services.asaas.api_key' => 'test-key',
        ]);
        Http::fake([
            'https://asaas.example/v3/finance/balance' => Http::sequence()
                ->push(['balance' => 0])
                ->pushStatus(503),
        ]);

        $athlete = User::factory()->unpaid()->create(['name' => 'Atleta Pagante']);
        ChallengePayment::create([
            'user_id' => $athlete->id,
            'asaas_payment_id' => 'pay_dashboard_test',
            'status' => ChallengePayment::PENDING,
            'billing_type' => 'PIX',
            'value' => 79.90,
            'due_date' => today()->addDays(2),
            'invoice_url' => 'https://asaas.example/private-invoice',
        ]);

        foreach ([UserRole::Administrador, UserRole::Nutricionista] as $role) {
            $response = $this->actingAs(User::factory()->ofRole($role)->create())
                ->get(route('admin.payments'));

            $response->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('dashboard/Payments')
                    ->where('payments.data.0.athlete', 'Atleta Pagante')
                    ->where('payments.data.0.asaasPaymentId', 'pay_dashboard_test')
                    ->where('payments.data.0.status', ChallengePayment::PENDING)
                    ->where('payments.data.0.billingType', 'PIX')
                    ->where('statusSummary.0.category', ChallengePayment::PENDING)
                    ->where('statusSummary.0.quantity', 1)
                    ->where('statusSummary.0.amount', 79.9)
                    ->where('billingTypeSummary.0.category', 'PIX')
                    ->where('billingTypeSummary.0.quantity', 1)
                    ->where('billingTypeSummary.0.amount', 79.9)
                    ->where('canViewBalance', $role === UserRole::Administrador)
                    ->missing('payments.data.0.invoice_url'));

            $headers = [
                'X-Inertia' => 'true',
                'X-Inertia-Version' => $response->viewData('page')['version'],
                'X-Inertia-Partial-Component' => 'dashboard/Payments',
                'X-Inertia-Partial-Data' => 'accountBalance',
            ];

            if ($role === UserRole::Administrador) {
                $this->get(route('admin.payments'), $headers)
                    ->assertOk()
                    ->assertJsonPath('props.accountBalance', 0);

                $this->get(route('admin.payments'), $headers)
                    ->assertOk()
                    ->assertJsonPath('props.accountBalance', null);
            } else {
                $this->get(route('admin.payments'), $headers)
                    ->assertOk()
                    ->assertJsonPath('props.accountBalance', null);
            }
        }

        Http::assertSentCount(2);

        $this->actingAs(User::factory()->ofRole(UserRole::Coach)->create())
            ->get(route('admin.payments'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.payments'))
            ->assertForbidden();
    }

    public function test_payment_summary_includes_pix_even_without_pix_payments(): void
    {
        $this->actingAs(User::factory()->ofRole(UserRole::Nutricionista)->create())
            ->get(route('admin.payments'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('billingTypeSummary.0.category', 'PIX')
                ->where('billingTypeSummary.0.quantity', 0)
                ->where('billingTypeSummary.0.amount', 0));
    }

    public function test_admin_can_register_a_user_of_any_type(): void
    {
        $coachId = UserType::idFor(UserRole::Coach);

        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->create())
            ->post('/admin/usuarios', [
                'name' => 'Nova Coach',
                'email' => 'coach@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'user_type_id' => $coachId,
            ])
            ->assertRedirect('/admin/usuarios');

        $created = User::query()->where('email', 'coach@example.com')->first();
        $this->assertNotNull($created);
        $this->assertSame($coachId, $created->user_type_id);
        $this->assertNotNull($created->email_verified_at);

        $this->actingAs(User::factory()->create())
            ->get('/admin/usuarios/novo')
            ->assertForbidden();
    }

    public function test_only_the_team_can_open_the_athlete_list(): void
    {
        User::factory()->create(['name' => 'Atleta Teste']);

        $this->actingAs(User::factory()->ofRole(UserRole::Coach)->create())
            ->get('/equipe/atletas')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard/Athletes')
                ->where('athletes.0.name', 'Atleta Teste'));

        $this->actingAs(User::factory()->create())
            ->get('/equipe/atletas')
            ->assertForbidden();

        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->create())
            ->get('/equipe/atletas')
            ->assertForbidden();
    }
}

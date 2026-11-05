<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CourtesyAthleteTest extends TestCase
{
    use RefreshDatabase;

    private function registration(bool $courtesy = true): array
    {
        return ['name' => 'Atleta Cortesia', 'email' => 'cortesia@example.com', 'password' => 'password', 'password_confirmation' => 'password', 'user_type_id' => UserType::idFor(UserRole::Atleta), 'courtesy' => $courtesy];
    }

    public function test_admin_can_create_a_courtesy_athlete_without_payment_or_asaas_requests(): void
    {
        Http::fake();
        $admin = User::factory()->ofRole(UserRole::Administrador)->create();
        $this->actingAs($admin)->post('/admin/usuarios', $this->registration())->assertRedirect('/admin/usuarios');
        $athlete = User::where('email', 'cortesia@example.com')->firstOrFail();
        $this->assertTrue($athlete->isCourtesy());
        $this->assertFalse($athlete->hasPaidChallenge());
        $this->assertTrue($athlete->hasChallengeAccess());
        $this->assertSame($admin->id, $athlete->challenge_courtesy_by);
        $this->assertDatabaseCount('challenge_payments', 0);
        $this->actingAs($athlete)->get('/atleta/ponto-de-partida/primeiro-dia')->assertRedirect('/atleta/boas-vindas');
        $this->post('/atleta/boas-vindas')->assertRedirect('/dashboard');
        $this->actingAs($athlete->refresh());
        $this->get('/atleta/ponto-de-partida/primeiro-dia')->assertOk();
        foreach (['/pagamento', '/pagamento/pagar', '/pagamento/retorno'] as $path) {
            $this->get($path)->assertRedirect(route('dashboard', absolute: false));
        }
        $this->post('/pagamento')->assertRedirect(route('dashboard', absolute: false));
        Http::assertNothingSent();
    }

    public function test_regular_athlete_created_by_admin_still_needs_payment(): void
    {
        $admin = User::factory()->ofRole(UserRole::Administrador)->create();
        $this->actingAs($admin)->post('/admin/usuarios', $this->registration(false))->assertRedirect('/admin/usuarios');
        $athlete = User::where('email', 'cortesia@example.com')->firstOrFail();
        $this->assertFalse($athlete->hasChallengeAccess());
        $this->actingAs($athlete)->get('/dashboard')->assertRedirect(route('pagamento.show', absolute: false));
    }

    public function test_only_admin_can_grant_or_remove_courtesy_for_an_existing_athlete(): void
    {
        $athlete = User::factory()->unpaid()->create();
        $this->patch('/admin/usuarios/'.$athlete->id.'/cortesia', ['courtesy' => true])->assertRedirect('/login');
        foreach ([UserRole::Atleta, UserRole::Coach, UserRole::Nutricionista, UserRole::Marketing] as $role) {
            $this->actingAs(User::factory()->ofRole($role)->create())
                ->patch('/admin/usuarios/'.$athlete->id.'/cortesia', ['courtesy' => true])->assertForbidden();
            $this->post('/admin/usuarios', $this->registration())->assertForbidden();
        }
        $this->assertFalse($athlete->fresh()->isCourtesy());
        $admin = User::factory()->ofRole(UserRole::Administrador)->create();
        $this->actingAs($admin)->patch('/admin/usuarios/'.$athlete->id.'/cortesia', ['courtesy' => true])->assertRedirect('/admin/usuarios');
        $this->assertTrue($athlete->fresh()->hasChallengeAccess());
        $this->get('/admin/usuarios')->assertInertia(fn (Assert $page) => $page->has('users'));
        $this->patch('/admin/usuarios/'.$athlete->id.'/cortesia', ['courtesy' => false])->assertRedirect('/admin/usuarios');
        $this->assertFalse($athlete->fresh()->hasChallengeAccess());
    }

    public function test_courtesy_is_exclusive_to_athletes(): void
    {
        $admin = User::factory()->ofRole(UserRole::Administrador)->create();
        $data = $this->registration();
        $data['user_type_id'] = UserType::idFor(UserRole::Coach);
        $this->actingAs($admin)->post('/admin/usuarios', $data)->assertSessionHasErrors('courtesy');
        $this->assertDatabaseMissing('users', ['email' => 'cortesia@example.com']);
        $this->patch('/admin/usuarios/'.$admin->id.'/cortesia', ['courtesy' => true])->assertForbidden();
    }

    public function test_public_registration_cannot_grant_itself_courtesy(): void
    {
        $data = $this->registration();
        $data['challenge_courtesy_at'] = now()->toDateTimeString();
        $data['challenge_paid_at'] = now()->toDateTimeString();
        $this->post('/register', $data)->assertRedirect(route('pagamento.show', absolute: false));
        $athlete = User::where('email', 'cortesia@example.com')->firstOrFail();
        $this->assertFalse($athlete->hasChallengeAccess());
        $this->assertNull($athlete->challenge_courtesy_by);
    }

    public function test_removing_courtesy_does_not_remove_a_paid_athletes_access(): void
    {
        $admin = User::factory()->ofRole(UserRole::Administrador)->create();
        $athlete = User::factory()->create(['challenge_courtesy_at' => now()]);
        $this->actingAs($admin)->patch('/admin/usuarios/'.$athlete->id.'/cortesia', ['courtesy' => false])->assertRedirect('/admin/usuarios');
        $this->assertFalse($athlete->fresh()->isCourtesy());
        $this->assertTrue($athlete->fresh()->hasPaidChallenge());
        $this->assertTrue($athlete->fresh()->hasChallengeAccess());
    }

    public function test_courtesy_login_goes_to_dashboard_and_profile_identifies_it(): void
    {
        $athlete = User::factory()->unpaid()->create(['challenge_courtesy_at' => now()]);
        $this->post('/login', ['email' => $athlete->email, 'password' => 'password'])->assertRedirect(route('dashboard', absolute: false));
        $this->get('/settings/profile')->assertInertia(fn (Assert $page) => $page
            ->where('paymentInfo.status', 'cortesia')->where('paymentInfo.value', 0)->where('paymentInfo.dueDate', null));
    }

    public function test_profile_update_cannot_grant_courtesy(): void
    {
        $athlete = User::factory()->unpaid()->create();
        $this->actingAs($athlete)->patch('/settings/profile', [
            'name' => $athlete->name, 'email' => $athlete->email,
            'challenge_courtesy_at' => now()->toDateTimeString(), 'courtesy' => true,
        ])->assertSessionHasNoErrors();
        $this->assertFalse($athlete->fresh()->hasChallengeAccess());
    }
}
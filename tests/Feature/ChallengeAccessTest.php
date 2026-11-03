<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AthleteProfile;
use App\Models\User;
use App\Support\ChallengeAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChallengeAccessTest extends TestCase
{
    use RefreshDatabase;

    private function athlete(): User
    {
        $user = User::factory()->create();
        AthleteProfile::create(['user_id' => $user->id, 'welcomed_at' => now(), 'goals_set_at' => now()]);

        return $user;
    }

    private function mode(string $feature, string $mode): void
    {
        DB::table('challenge_access_settings')->updateOrInsert(['feature' => $feature], ['mode' => $mode]);
    }

    public function test_evolution_menu_availability_follows_the_release_date(): void
    {
        $this->travelTo(Carbon::parse('2026-11-07 12:00:00', ChallengeAccess::TIMEZONE));
        $this->actingAs($this->athlete())->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('evolutionAccess.releaseDate', '08/11/2026')
            ->where('evolutionAccess.pages.dias', false)
            ->where('evolutionAccess.pages.conquistas', false)
            ->where('evolutionAccess.pages.resumo', false));
        $this->travelTo(Carbon::parse('2026-11-08 00:00:00', ChallengeAccess::TIMEZONE));
        $this->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('evolutionAccess.pages.dias', true)
            ->where('evolutionAccess.pages.conquistas', true)
            ->where('evolutionAccess.pages.resumo', true));
    }

    public function test_evolution_menu_honors_individual_admin_overrides(): void
    {
        $this->travelTo(Carbon::parse('2026-11-02 12:00:00', ChallengeAccess::TIMEZONE));
        $this->mode('conquistas', 'open');
        $this->actingAs($this->athlete())->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('evolutionAccess.pages.dias', false)
            ->where('evolutionAccess.pages.conquistas', false)
            ->where('evolutionAccess.pages.resumo', false));
        $this->mode('conquistas', 'closed');
        $this->travelTo(Carbon::parse('2026-11-09 12:00:00', ChallengeAccess::TIMEZONE));
        $this->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('evolutionAccess.pages.dias', true)
            ->where('evolutionAccess.pages.conquistas', false));
    }

    public function test_shopping_is_hidden_and_protected_before_the_preview_date(): void
    {
        $this->travelTo(Carbon::parse('2026-11-07 23:59:59', ChallengeAccess::TIMEZONE));
        $user = $this->athlete();
        $user->athleteProfile->update(['shopping_lists' => ['1' => ['items' => ['Ovos'], 'extras' => []]]]);
        $this->actingAs($user)->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('evolutionAccess.pages.shopping', false)->where('evolutionAccess.pages.dias', false));
        foreach (['/objetivos/alimentacao/compras', '/objetivos/alimentacao/compras/nova', '/objetivos/alimentacao/compras/1/editar'] as $path) {
            $this->get($path)->assertInertia(fn (Assert $page) => $page->component('dashboard/EvolutionLocked')->missing('lists')->missing('list'));
        }
        $this->post('/objetivos/alimentacao/compras', ['week' => 2, 'items' => ['Novo']])->assertForbidden();
        $this->put('/objetivos/alimentacao/compras', ['week' => 1, 'items' => ['Mudança']])->assertForbidden();
        $this->assertSame(['Ovos'], $user->fresh()->athleteProfile->shopping_lists['1']['items']);
    }

    public function test_shopping_opens_on_november_eighth_and_obeys_admin_overrides(): void
    {
        $this->travelTo(Carbon::parse('2026-11-08 00:00:00', ChallengeAccess::TIMEZONE));
        $this->actingAs($this->athlete())->get('/objetivos/alimentacao/compras')
            ->assertInertia(fn (Assert $page) => $page->component('Compras')->where('evolutionAccess.pages.shopping', true));
        $this->mode('shopping', 'closed');
        $this->get('/objetivos/alimentacao/compras')->assertInertia(fn (Assert $page) => $page->component('dashboard/EvolutionLocked'));
        $this->travelTo(Carbon::parse('2026-11-03 12:00:00', ChallengeAccess::TIMEZONE));
        $this->mode('shopping', 'open');
        $this->get('/objetivos/alimentacao/compras')->assertInertia(fn (Assert $page) => $page->component('dashboard/EvolutionLocked'));
    }

    public function test_first_goal_submission_returns_to_dashboard_before_release(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 12:00:00', ChallengeAccess::TIMEZONE));
        $user = User::factory()->create();
        AthleteProfile::create(['user_id' => $user->id, 'welcomed_at' => now()]);
        $this->actingAs($user)->put('/atleta/metas', ['movement_level' => 'iniciante', 'water_liters' => 2.5])
            ->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->component('dashboard/Atleta')->where('evolutionAccess.pages.dias', false));
        $this->get('/atleta/evolucao/dias')->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertInertia(fn (Assert $page) => $page->has('accessNotice'));
    }

    public function test_admin_can_preview_all_pages_without_being_an_athlete_or_opening_them_globally(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 12:00:00', ChallengeAccess::TIMEZONE));
        foreach (array_keys(ChallengeAccess::FEATURES) as $feature) {
            $this->mode($feature, 'closed');
        }
        $admin = User::factory()->ofRole(UserRole::Administrador)->unpaid()->create();
        foreach (['dias', 'conquistas', 'resumo'] as $tab) {
            $this->actingAs($admin)->get('/atleta/evolucao/'.$tab)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('dashboard/Evolucao')->where('adminPreview', true)
                ->where('tab', $tab)->where('canEdit', false)->where('canComment', false)
                ->where('access.editableDays', [])->where('evolutionAccess.pages.dias', true));
        }
        $this->get('/objetivos/alimentacao/compras')->assertInertia(fn (Assert $page) => $page
            ->component('Compras')->where('adminPreview', true)->where('canSave', false)->where('lists', []));
        $this->put('/atleta/dias', ['days' => [['day' => 1]]])->assertForbidden();
        $this->post('/atleta/dias/1/foto-treino', [])->assertForbidden();
        $this->post('/atleta/conquistas', ['type' => 'cantinho', 'detail' => 'Teste'])->assertForbidden();
        $this->post('/objetivos/alimentacao/compras', ['week' => 1, 'items' => ['Ovos']])->assertForbidden();
        $this->put('/atleta/metas', [])->assertForbidden();
        $this->assertDatabaseCount('checkins', 0);
        $this->assertDatabaseCount('achievements', 0);
        $this->assertDatabaseCount('athlete_profiles', 0);
        $athlete = $this->athlete();
        $this->actingAs($athlete)->get('/atleta/evolucao/dias?adminPreview=1')->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertInertia(fn (Assert $page) => $page->where('evolutionAccess.pages.dias', false));
        $this->assertSame('closed', (new ChallengeAccess)->modes()['dias']);
    }

    public function test_other_roles_cannot_use_the_admin_evolution_preview(): void
    {
        $this->get('/atleta/evolucao/dias')->assertRedirect('/login');
        foreach ([UserRole::Coach, UserRole::Nutricionista, UserRole::Marketing] as $role) {
            $this->actingAs(User::factory()->ofRole($role)->create())->get('/atleta/evolucao/dias?adminPreview=1')->assertForbidden();
        }
    }

    public function test_diary_is_not_accessible_before_november_eighth(): void
    {
        $this->travelTo(Carbon::parse('2026-11-07 23:59:59', ChallengeAccess::TIMEZONE));
        $this->actingAs($this->athlete())->get('/atleta/evolucao/dias')
            ->assertRedirect('/dashboard');
    }

    public function test_preview_shows_all_thirty_days_disabled_on_november_eighth(): void
    {
        $this->travelTo(Carbon::parse('2026-11-08 00:00:00', ChallengeAccess::TIMEZONE));
        $this->actingAs($this->athlete())->get('/atleta/evolucao/dias')
            ->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('dashboard/Evolucao')->has('days', 30)
                ->where('access.editableDays', [])->where('access.releaseDate', '08/11/2026'));
    }

    public function test_only_the_corresponding_day_accepts_entries_and_mixed_batches_are_atomic(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 12:00:00', ChallengeAccess::TIMEZONE));
        $this->actingAs($this->athlete())->get('/atleta/evolucao/dias')
            ->assertInertia(fn (Assert $page) => $page->where('access.editableDays', [2]));
        foreach ([1, 3, 30] as $day) {
            $this->put('/atleta/dias', ['days' => [['day' => $day]]])->assertSessionHasErrors('days');
        }
        $this->put('/atleta/dias', ['days' => [['day' => 2], ['day' => 3]]])->assertSessionHasErrors('days');
        $this->assertDatabaseCount('checkins', 0);
        $this->put('/atleta/dias', ['days' => [['day' => 2, 'water' => true]]])->assertRedirect('/atleta/evolucao/dias');
        $this->assertDatabaseHas('checkins', ['day' => 2, 'water' => true]);
    }

    public function test_brasilia_midnight_changes_the_editable_day(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 02:59:59', 'UTC'));
        $this->assertTrue((new ChallengeAccess)->dayEditable(1));
        $this->assertFalse((new ChallengeAccess)->dayEditable(2));
        $this->travelTo(Carbon::parse('2026-11-10 03:00:00', 'UTC'));
        $this->assertFalse((new ChallengeAccess)->dayEditable(1));
        $this->assertTrue((new ChallengeAccess)->dayEditable(2));
    }

    public function test_photos_cannot_be_added_or_deleted_on_a_locked_day(): void
    {
        Storage::fake('public');
        $this->travelTo(Carbon::parse('2026-11-09 12:00:00', ChallengeAccess::TIMEZONE));
        $user = $this->athlete();
        foreach (['foto-treino' => 'workout_photo', 'foto-refeicao' => 'meal_photo'] as $route => $column) {
            Storage::disk('public')->put($route.'.jpg', 'existing');
            $user->checkins()->updateOrCreate(['day' => 2], [$column => $route.'.jpg']);
            $this->actingAs($user)->post('/atleta/dias/2/'.$route, ['photo' => UploadedFile::fake()->image('foto.jpg')])
                ->assertSessionHasErrors('days');
            $this->delete('/atleta/dias/2/'.$route)->assertSessionHasErrors('days');
            Storage::disk('public')->assertExists($route.'.jpg');
            $this->assertDatabaseHas('checkins', ['day' => 2, $column => $route.'.jpg']);
        }
    }

    public function test_calendar_expires_after_the_last_day(): void
    {
        $this->travelTo(Carbon::parse('2026-12-08 23:59:59', ChallengeAccess::TIMEZONE));
        $this->assertTrue((new ChallengeAccess)->dayEditable(30));
        $this->travelTo(Carbon::parse('2026-12-09 00:00:00', ChallengeAccess::TIMEZONE));
        $this->assertSame([], (new ChallengeAccess)->payload()['editableDays']);
    }

    public function test_legacy_open_settings_never_bypass_the_athlete_calendar(): void
    {
        $this->travelTo(Carbon::parse('2026-11-03 12:00:00', ChallengeAccess::TIMEZONE));
        foreach (array_keys(ChallengeAccess::FEATURES) as $feature) {
            $this->mode($feature, 'open');
        }
        $this->assertFalse((new ChallengeAccess)->pageOpen('dias'));
        $this->assertFalse((new ChallengeAccess)->dayEditable(30));
        $this->actingAs($this->athlete())->get('/atleta/evolucao/dias')->assertRedirect('/dashboard');
        $this->put('/atleta/dias', ['days' => [['day' => 30]]])->assertSessionHasErrors('days');
        $this->assertDatabaseCount('checkins', 0);
        $this->travelTo(Carbon::parse('2026-11-09 12:00:00', ChallengeAccess::TIMEZONE));
        $this->assertTrue((new ChallengeAccess)->dayEditable(1));
        $this->assertFalse((new ChallengeAccess)->dayEditable(30));
    }

    public function test_individual_pages_and_achievement_writes_can_be_blocked(): void
    {
        $this->travelTo(Carbon::parse('2026-11-09 12:00:00', ChallengeAccess::TIMEZONE));
        $user = $this->athlete();
        $this->mode('conquistas', 'closed');
        $this->mode('resumo', 'closed');
        foreach (['conquistas', 'resumo'] as $tab) {
            $this->actingAs($user)->get('/atleta/evolucao/'.$tab)->assertRedirect('/dashboard');
        }
        $this->post('/atleta/conquistas', ['type' => 'cantinho', 'detail' => 'Teste'])->assertForbidden();
        $this->assertDatabaseCount('achievements', 0);
    }

    public function test_only_an_admin_can_view_or_change_access_controls(): void
    {
        foreach ([UserRole::Atleta, UserRole::Coach, UserRole::Nutricionista, UserRole::Marketing] as $role) {
            $this->actingAs(User::factory()->ofRole($role)->create())->get('/admin/liberacoes')->assertForbidden();
            $this->put('/admin/liberacoes', [])->assertForbidden();
        }
        $this->assertDatabaseCount('challenge_access_settings', 0);
    }

    public function test_saving_requires_the_admin_password_and_persists_all_modes(): void
    {
        $admin = User::factory()->ofRole(UserRole::Administrador)->create(['password' => Hash::make('senha-teste-admin')]);
        $modes = array_fill_keys(array_keys(ChallengeAccess::FEATURES), 'closed');
        $this->actingAs($admin)->get('/admin/liberacoes')->assertOk();
        $this->put('/admin/liberacoes', ['modes' => $modes, 'password' => 'incorreta'])->assertSessionHasErrors('password');
        $this->assertDatabaseCount('challenge_access_settings', 0);
        $this->put('/admin/liberacoes', ['modes' => $modes, 'password' => 'senha-teste-admin'])->assertRedirect('/admin/liberacoes');
        $this->assertDatabaseCount('challenge_access_settings', count(ChallengeAccess::FEATURES));
        $this->assertDatabaseHas('challenge_access_settings', ['feature' => 'entries', 'mode' => 'closed', 'updated_by' => $admin->id]);
        $modes['entries'] = 'open';
        $this->put('/admin/liberacoes', ['modes' => $modes, 'password' => 'senha-teste-admin'])->assertSessionHasErrors('modes.entries');
    }
}

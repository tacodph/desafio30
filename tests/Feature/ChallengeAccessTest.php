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

    public function test_diary_is_not_accessible_before_november_eighth(): void
    {
        $this->travelTo(Carbon::parse('2026-11-07 23:59:59', ChallengeAccess::TIMEZONE));
        $this->actingAs($this->athlete())->get('/atleta/evolucao/dias')
            ->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('dashboard/EvolutionLocked')->missing('days'));
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

    public function test_test_mode_requires_the_diary_page_to_be_open_and_closed_wins(): void
    {
        $this->travelTo(Carbon::parse('2026-11-02 12:00:00', ChallengeAccess::TIMEZONE));
        $this->mode('entries', 'open');
        $this->assertFalse((new ChallengeAccess)->dayEditable(30));
        $this->mode('dias', 'open');
        $this->assertTrue((new ChallengeAccess)->dayEditable(30));
        $this->actingAs($this->athlete())->put('/atleta/dias', ['days' => [['day' => 30]]])
            ->assertRedirect('/atleta/evolucao/dias');
        $this->mode('entries', 'closed');
        $this->assertFalse((new ChallengeAccess)->dayEditable(30));
        $this->mode('dias', 'closed');
        $this->get('/atleta/evolucao/dias')->assertInertia(fn (Assert $page) => $page->component('dashboard/EvolutionLocked'));
    }

    public function test_individual_pages_and_achievement_writes_can_be_blocked(): void
    {
        $this->travelTo(Carbon::parse('2026-11-09 12:00:00', ChallengeAccess::TIMEZONE));
        $user = $this->athlete();
        $this->mode('conquistas', 'closed');
        $this->mode('resumo', 'closed');
        foreach (['conquistas', 'resumo'] as $tab) {
            $this->actingAs($user)->get('/atleta/evolucao/'.$tab)
                ->assertInertia(fn (Assert $page) => $page->component('dashboard/EvolutionLocked'));
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
        $modes = array_fill_keys(array_keys(ChallengeAccess::FEATURES), 'open');
        $this->actingAs($admin)->get('/admin/liberacoes')->assertOk();
        $this->put('/admin/liberacoes', ['modes' => $modes, 'password' => 'incorreta'])->assertSessionHasErrors('password');
        $this->assertDatabaseCount('challenge_access_settings', 0);
        $this->put('/admin/liberacoes', ['modes' => $modes, 'password' => 'senha-teste-admin'])->assertRedirect('/admin/liberacoes');
        $this->assertDatabaseCount('challenge_access_settings', 4);
        $this->assertDatabaseHas('challenge_access_settings', ['feature' => 'entries', 'mode' => 'open', 'updated_by' => $admin->id]);
        $modes['entries'] = 'invalid';
        $this->put('/admin/liberacoes', ['modes' => $modes, 'password' => 'senha-teste-admin'])->assertSessionHasErrors('modes.entries');
    }
}

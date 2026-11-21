<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Challenge;
use App\Models\User;
use App\Support\ChallengeAccess;
use App\Support\ChallengeSheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChallengeSchedulingTest extends TestCase
{
    use RefreshDatabase;

    private function challenge(array $overrides = []): Challenge
    {
        return Challenge::create(array_replace(['name' => 'Desafio de 14 dias', 'default_start_date' => '2027-01-01',
            'duration_days' => 14, 'objectives' => ['water', 'movement'], 'accepting_participants' => true], $overrides));
    }

    public function test_existing_challenge_and_calendar_are_preserved(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('athlete.challenge'))->assertInertia(fn (Assert $page) => $page
            ->where('challenge.name', 'Desafio da Virada')->where('challenge.start_date', '2026-11-09')
            ->where('challenge.end_date', '2026-12-08')->where('challenge.duration_days', 30));
        $this->assertCount(30, ChallengeSheet::payload($user)['days']);
    }

    public function test_only_admin_can_create_a_challenge_and_choose_objectives(): void
    {
        $data = ['name' => 'Novo desafio', 'default_start_date' => '2027-02-03', 'duration_days' => 45,
            'objectives' => ['water', 'strength'], 'accepting_participants' => true];
        $this->actingAs(User::factory()->create())->post(route('admin.challenges.store'), $data)->assertForbidden();
        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->create())
            ->post(route('admin.challenges.store'), $data)->assertRedirect()->assertSessionHasNoErrors();
        $challenge = Challenge::where('name', 'Novo desafio')->firstOrFail();
        $this->assertSame('2027-03-19', $challenge->default_end_date);
        $this->assertSame(['water', 'strength'], $challenge->objectives);
        $this->post(route('admin.challenges.store'), [...$data, 'objectives' => ['invalido']])->assertSessionHasErrors('objectives.0');
    }

    public function test_start_can_change_before_any_daily_entry_but_measurements_and_goals_remain(): void
    {
        $this->travelTo(Carbon::parse('2026-11-16 12:00:00', ChallengeAccess::TIMEZONE));
        $user = User::factory()->create();
        $user->athleteProfile()->create(['weight' => 80, 'water_liters' => 2.8, 'goals_set_at' => now()]);
        $this->actingAs($user)->put(route('athlete.challenge.start'), ['start_date' => '2026-11-20', 'confirmed' => true])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->get(route('athlete.challenge'))->assertInertia(fn (Assert $page) => $page
            ->where('challenge.start_date', '2026-11-20')->where('challenge.end_date', '2026-12-19')->where('canChangeStart', false));
        $this->assertSame(80.0, (float) $user->fresh()->athleteProfile->weight);
        $this->assertSame('2026-11-20', ChallengeSheet::dateForDay(1, $user));
        $this->assertSame('2026-12-19', ChallengeSheet::dateForDay(30, $user));
        $this->assertFalse((new ChallengeAccess)->dayEditable(1));
        $this->travelTo(Carbon::parse('2026-11-20 00:00:00', ChallengeAccess::TIMEZONE));
        $this->assertTrue((new ChallengeAccess)->dayEditable(1));
    }

    public function test_first_daily_entry_or_photo_locks_start_date(): void
    {
        $this->travelTo(Carbon::parse('2026-11-16', ChallengeAccess::TIMEZONE));
        $user = User::factory()->create();
        $user->checkins()->create(['day' => 1, 'checked_in' => true]);
        $this->actingAs($user)->put(route('athlete.challenge.start'), ['start_date' => '2026-11-20', 'confirmed' => true])
            ->assertSessionHasErrors('start_date');
        $this->assertSame('2026-11-09', ChallengeSheet::startDate($user));
        $other = User::factory()->create();
        $other->checkins()->create(['day' => 1, 'meal_photo' => 'photos/test.jpg']);
        $this->actingAs($other)->put(route('athlete.challenge.start'), ['start_date' => '2026-11-20', 'confirmed' => true])
            ->assertSessionHasErrors('start_date');
    }

    public function test_new_challenge_has_its_own_profile_checkins_achievements_and_duration(): void
    {
        $this->travelTo(Carbon::parse('2027-01-02', ChallengeAccess::TIMEZONE));
        $user = User::factory()->create();
        $oldId = $user->active_challenge_id;
        $user->athleteProfile()->create(['weight' => 90, 'movement_minutes' => 20]);
        $user->checkins()->create(['day' => 1, 'checked_in' => true, 'movement' => true]);
        $user->achievements()->create(['type' => 'cantinho', 'achieved_on' => '2026-11-09', 'detail' => 'Anterior']);
        $challenge = $this->challenge();
        $this->actingAs($user)->post(route('athlete.challenge.join', $challenge))->assertRedirect()->assertSessionHasNoErrors();
        $user = $user->fresh();
        $this->assertSame($challenge->id, $user->active_challenge_id);
        $this->assertNull($user->athleteProfile);
        $this->assertCount(0, $user->checkins);
        $this->assertCount(0, $user->achievements);
        $profile = $user->athleteProfile()->create(['weight' => 75]);
        $user->checkins()->create(['day' => 1, 'checked_in' => true, 'water' => true]);
        $this->assertSame($challenge->id, $profile->challenge_id);
        $payload = ChallengeSheet::payload($user->fresh());
        $this->assertCount(14, $payload['days']);
        $this->assertCount(2, $payload['weeks']);
        $this->assertSame('01/01', $payload['days'][0]['date']);
        $this->assertSame(['water', 'movement'], $payload['goals']['enabled']);
        $this->assertDatabaseHas('athlete_profiles', ['user_id' => $user->id, 'challenge_id' => $oldId, 'weight' => 90]);
        $this->assertDatabaseHas('checkins', ['user_id' => $user->id, 'challenge_id' => $oldId, 'day' => 1]);
        $this->get(route('athlete.challenge'))->assertInertia(fn (Assert $page) => $page->has('history', 2));
    }

    public function test_cannot_join_another_challenge_while_current_started_challenge_is_running(): void
    {
        $this->travelTo(Carbon::parse('2026-11-16', ChallengeAccess::TIMEZONE));
        $user = User::factory()->create();
        $originalId = $user->active_challenge_id;
        $user->checkins()->create(['day' => 1, 'checked_in' => true]);
        $this->actingAs($user)->post(route('athlete.challenge.join', $this->challenge()))->assertSessionHasErrors('challenge');
        $this->assertSame($originalId, $user->fresh()->active_challenge_id);
    }

    public function test_admin_cannot_change_schedule_or_objectives_after_checkin(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::findOrFail($user->active_challenge_id);
        $user->checkins()->create(['day' => 1]);
        $data = ['name' => $challenge->name, 'default_start_date' => '2026-11-10', 'duration_days' => 15,
            'objectives' => ['water'], 'accepting_participants' => true];
        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->create())
            ->put(route('admin.challenges.update', $challenge), $data)->assertSessionHasErrors('challenge');
        $this->assertSame(30, $challenge->fresh()->duration_days);
    }

    public function test_new_default_only_applies_to_new_users(): void
    {
        $existing = User::factory()->create();
        $id = $existing->active_challenge_id;
        $next = $this->challenge();
        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->create())
            ->put(route('admin.challenges.default', $next))->assertRedirect();
        $this->assertSame($id, $existing->fresh()->active_challenge_id);
        $this->assertSame($next->id, User::factory()->create()->active_challenge_id);
    }

    public function test_day_limits_and_objectives_follow_the_selected_challenge(): void
    {
        $this->travelTo(Carbon::parse('2027-01-01', ChallengeAccess::TIMEZONE));
        $user = User::factory()->create();
        $next = $this->challenge(['duration_days' => 8]);
        $this->actingAs($user)->post(route('athlete.challenge.join', $next));
        $user = $user->fresh();
        $user->forceFill(['welcome_acknowledged_at' => now()])->save();
        $user->athleteProfile()->create(['welcomed_at' => now(), 'goals_set_at' => now()]);
        DB::table('challenge_participations')->where('user_id', $user->id)->where('challenge_id', $user->active_challenge_id)->update(['start_date_confirmed_at' => now()]);
        $this->actingAs($user)->get(route('athlete.progress.tab', 'dias'))->assertInertia(fn (Assert $page) => $page
            ->has('days', 8)->has('weeks', 2)->where('goals.enabled', ['water', 'movement']));
        $this->patch(route('athlete.days.goal.update', ['day' => 1, 'goal' => 'sleep']), ['details' => ['sleep_hours' => 8]])->assertForbidden();
        $this->patch(route('athlete.days.goal.update', ['day' => 1, 'goal' => 'water']), ['details' => ['water_ml' => 2500]])->assertRedirect();
        $this->put('/atleta/dias', ['days' => [['day' => 9]]])->assertSessionHasErrors('days.0.day');
    }

    public function test_longer_challenge_has_a_partial_last_week_and_rejects_earlier_start(): void
    {
        $this->travelTo(Carbon::parse('2027-01-01', ChallengeAccess::TIMEZONE));
        $user = User::factory()->create();
        $challenge = $this->challenge(['duration_days' => 45]);
        $this->actingAs($user)->post(route('athlete.challenge.join', $challenge));
        $user = $user->fresh();
        $this->actingAs($user);
        $payload = ChallengeSheet::payload($user);
        $this->assertCount(45, $payload['days']);
        $this->assertCount(7, $payload['weeks']);
        $this->assertSame('14/02', $payload['days'][44]['date']);
        $this->assertSame(7, $payload['days'][44]['week']);
        $this->put(route('athlete.challenge.start'), ['start_date' => '2026-12-31', 'confirmed' => true])->assertSessionHasErrors('start_date');
        $this->get(route('nutri.monitoring.print'))->assertInertia(fn (Assert $page) => $page
            ->where('totalDays', 45)->where('startDate', '2027-01-01')->where('objectives', ['water', 'movement']));
    }

    public function test_private_video_comments_and_admin_access_modes_are_separate_per_challenge(): void
    {
        $this->travelTo(Carbon::parse('2027-01-01', ChallengeAccess::TIMEZONE));
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/comentarios-privados', ['context' => 'video:jantar', 'body' => 'Comentário anterior'])->assertCreated();
        $next = $this->challenge();
        $this->post(route('athlete.challenge.join', $next));
        $this->actingAs($user->fresh())->getJson('/comentarios-privados?context=video:jantar')->assertJsonCount(0, 'comments.data');
        $admin = User::factory()->ofRole(UserRole::Administrador)->create();
        $this->actingAs($admin)->put(route('challenges.view', $next));
        $modes = array_fill_keys(array_keys(ChallengeAccess::FEATURES), 'calendar');
        $modes['alimentacao'] = 'closed';
        $this->put(route('admin.challenge-access.update'), ['modes' => $modes, 'password' => 'password'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('challenge_feature_settings', ['challenge_id' => $next->id, 'feature' => 'alimentacao', 'mode' => 'closed']);
        $this->assertDatabaseMissing('challenge_access_settings', ['feature' => 'alimentacao', 'mode' => 'closed']);
    }
}

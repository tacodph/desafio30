<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChallengeStartChoiceTest extends TestCase
{
    use RefreshDatabase;

    private function athlete(bool $withGoals = true): User
    {
        $this->travelTo(Carbon::parse('2026-11-21 12:00:00', 'America/Sao_Paulo'));
        $user = User::factory()->create();
        $user->forceFill(['welcome_acknowledged_at' => now()])->save();
        $user->athleteProfile()->create(['welcomed_at' => now(), 'goals_set_at' => $withGoals ? now() : null]);

        return $user;
    }

    public function test_saving_goals_opens_start_choice(): void
    {
        $user = $this->athlete(false);
        $this->actingAs($user)->put('/atleta/metas', ['movement_level' => 'iniciante', 'water_liters' => 2.5])
            ->assertSessionHasNoErrors()->assertRedirect('/atleta/meu-desafio');
        $this->get('/atleta/meu-desafio')->assertInertia(fn (Assert $page) => $page
            ->where('needsStartChoice', true)->where('canChangeStart', true)->where('startConfirmed', false)
            ->where('challenge.duration_days', 30));
    }

    public function test_existing_goals_without_checkin_redirect_days_to_start_choice_and_prevent_direct_writes(): void
    {
        $user = $this->athlete();
        $this->actingAs($user)->get('/atleta/evolucao/dias')->assertRedirect('/atleta/meu-desafio');
        $this->patch('/atleta/dias/13/objetivos/water', ['details' => ['water_ml' => 2500]])->assertRedirect('/atleta/meu-desafio');
        $this->put('/atleta/dias', ['days' => [['day' => 13]]])->assertRedirect('/atleta/meu-desafio');
        $this->assertDatabaseCount('checkins', 0);
    }

    public function test_confirmation_is_required_and_date_can_only_be_saved_once_even_without_checkin(): void
    {
        $user = $this->athlete();
        $this->actingAs($user)->put('/atleta/meu-desafio/inicio', ['start_date' => '2026-11-21'])->assertSessionHasErrors('confirmed');
        $this->put('/atleta/meu-desafio/inicio', ['start_date' => '2026-11-21', 'confirmed' => true])
            ->assertSessionHasNoErrors()->assertRedirect('/atleta/evolucao/dias');
        $this->get('/atleta/evolucao/dias')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('dashboard/Evolucao')->where('days.0.date', '21/11'));
        $this->get('/atleta/meu-desafio')->assertInertia(fn (Assert $page) => $page
            ->where('canChangeStart', false)->where('startConfirmed', true)->where('needsStartChoice', false)
            ->where('challenge.end_date', '2026-12-20'));
        $this->assertDatabaseCount('checkins', 0);
        $this->put('/atleta/meu-desafio/inicio', ['start_date' => '2026-11-22', 'confirmed' => true])->assertSessionHasErrors('start_date');
        $this->assertDatabaseHas('challenge_participations', ['user_id' => $user->id, 'start_date' => '2026-11-21']);
    }

    public function test_athlete_who_already_started_does_not_need_to_choose_again(): void
    {
        $user = $this->athlete();
        $user->checkins()->create(['day' => 1, 'checked_in' => true]);
        $this->actingAs($user)->get('/atleta/evolucao/dias')->assertOk();
        $this->put('/atleta/meu-desafio/inicio', ['start_date' => '2026-11-21', 'confirmed' => true])->assertSessionHasErrors('start_date');
    }

    public function test_confirming_original_default_also_uses_the_one_time_choice(): void
    {
        $user = $this->athlete();
        $this->travelTo(Carbon::parse('2026-11-09 12:00:00', 'America/Sao_Paulo'));
        $this->actingAs($user)->put('/atleta/meu-desafio/inicio', ['start_date' => '2026-11-09', 'confirmed' => true])->assertSessionHasNoErrors();
        $this->assertNotNull(DB::table('challenge_participations')->where('user_id', $user->id)->value('start_date_confirmed_at'));
        $this->put('/atleta/meu-desafio/inicio', ['start_date' => '2026-11-10', 'confirmed' => true])->assertSessionHasErrors('start_date');
    }
}

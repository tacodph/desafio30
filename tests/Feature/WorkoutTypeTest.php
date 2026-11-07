<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AthleteProfile;
use App\Models\User;
use App\Models\WorkoutType;
use App\Support\ChallengeAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkoutTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_register_a_modality_that_can_be_saved_by_an_athlete(): void
    {
        $admin = User::factory()->ofRole(UserRole::Administrador)->create();
        $this->actingAs($admin)->post(route('admin.workout-types.store'), ['name' => ' Pilates '])
            ->assertRedirect(route('admin.workout-types'));
        $this->assertDatabaseHas('workout_types', ['name' => 'Pilates']);
        $this->post(route('admin.workout-types.store'), ['name' => 'pilates'])->assertSessionHasErrors('name');

        $this->get('/atleta/evolucao/dias')
            ->assertInertia(fn (Assert $page) => $page->where('workoutTypes', fn ($types) => in_array('Pilates', $types->all(), true)));

        $athlete = User::factory()->create();
        AthleteProfile::create(['user_id' => $athlete->id, 'welcomed_at' => now(), 'goals_set_at' => now()]);
        $this->travelTo(Carbon::parse('2026-11-09 12:00:00', ChallengeAccess::TIMEZONE));

        $this->actingAs($athlete)->patch('/atleta/dias/1/objetivos/movement', [
            'details' => ['workouts' => [['type' => 'Pilates', 'minutes' => 45]]],
        ])->assertSessionHasNoErrors()->assertRedirect('/atleta/evolucao/dias');

        $this->assertSame('Pilates', $athlete->checkins()->firstOrFail()->details['workouts'][0]['type']);
        $this->get(route('admin.workout-types'))->assertForbidden();
        $this->post(route('admin.workout-types.store'), ['name' => 'Tênis'])->assertForbidden();
        $this->assertFalse(WorkoutType::where('name', 'Tênis')->exists());
    }
}

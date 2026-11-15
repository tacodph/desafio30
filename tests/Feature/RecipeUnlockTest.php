<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ChallengeAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RecipeUnlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipes_are_released_without_waiting_for_seven_complete_days(): void
    {
        $this->travelTo(Carbon::parse('2026-12-08 12:00:00', ChallengeAccess::TIMEZONE));
        $athlete = User::factory()->create();
        foreach ([1, 3, 5, 7, 9, 11] as $day) {
            $athlete->checkins()->create(['day' => $day, 'movement' => true, 'water' => true, 'sleep' => true, 'strength' => true, 'skill' => true]);
        }
        $this->actingAs($athlete)->get('/cantinho-da-nutri')->assertInertia(fn (Assert $page) => $page
            ->where('recipeUnlock.completedDays', 6)->where('recipeUnlock.unlocked', true));
        $athlete->checkins()->create(['day' => 13, 'movement' => true, 'water' => true, 'sleep' => true, 'strength' => true, 'skill' => true]);
        $this->get('/cantinho-da-nutri')->assertInertia(fn (Assert $page) => $page
            ->where('recipeUnlock.requiredDays', 7)->where('recipeUnlock.completedDays', 7)->where('recipeUnlock.unlocked', true));
    }

    public function test_recipes_open_today_even_without_complete_days(): void
    {
        $this->travelTo(Carbon::parse('2026-11-15 12:00:00', ChallengeAccess::TIMEZONE));
        $this->actingAs(User::factory()->create())->get('/cantinho-da-nutri')->assertInertia(fn (Assert $page) => $page
            ->where('recipeUnlock.completedDays', 0)->where('recipeUnlock.unlocked', true));
    }

    public function test_incomplete_future_and_other_athletes_days_do_not_count(): void
    {
        $this->travelTo(Carbon::parse('2026-11-09 12:00:00', ChallengeAccess::TIMEZONE));
        $athlete = User::factory()->create();
        $athlete->checkins()->create(['day' => 1, 'movement' => true, 'water' => true, 'sleep' => true, 'strength' => true, 'skill' => false]);
        $athlete->checkins()->create(['day' => 2, 'movement' => true, 'water' => true, 'sleep' => true, 'strength' => true, 'skill' => true]);
        User::factory()->create()->checkins()->create(['day' => 1, 'movement' => true, 'water' => true, 'sleep' => true, 'strength' => true, 'skill' => true]);
        $this->actingAs($athlete)->get('/cantinho-da-nutri')->assertInertia(fn (Assert $page) => $page
            ->where('recipeUnlock.completedDays', 0)->where('recipeUnlock.unlocked', false));
    }
}

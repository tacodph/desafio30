<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\ChallengeAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NutritionAccessTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        '/cantinho-da-nutri' => 'ComoCombinarAlimentos',
        '/alimentacao/monte-seu-prato' => 'MonteSeuPrato',
        '/objetivos/alimentacao/compras' => 'Compras',
    ];

    private function mode(string $feature, string $mode): void
    {
        DB::table('challenge_access_settings')->updateOrInsert(['feature' => $feature], ['mode' => $mode]);
    }

    public function test_nutrition_pages_and_menu_availability_follow_brasilia_release_date(): void
    {
        $this->travelTo(Carbon::parse('2026-11-07 23:59:59', ChallengeAccess::TIMEZONE));
        $this->actingAs(User::factory()->create());
        foreach (self::PAGES as $url => $component) {
            $this->get($url)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('dashboard/EvolutionLocked')
                ->where('evolutionAccess.pages.alimentacao', false)
                ->where('evolutionAccess.pages.nutri', false)
                ->where('evolutionAccess.pages.shopping', false));
        }
        $this->get('/objetivos/alimentacao/compras/nova')->assertInertia(fn (Assert $page) => $page->component('dashboard/EvolutionLocked'));
        $this->get('/alimentacao/como-combinar')->assertRedirect('/cantinho-da-nutri');

        $this->travelTo(Carbon::parse('2026-11-08 00:00:00', ChallengeAccess::TIMEZONE));
        foreach (self::PAGES as $url => $component) {
            $this->get($url)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component($component)
                ->where('evolutionAccess.pages.alimentacao', true)
                ->where('evolutionAccess.pages.nutri', true));
        }
    }

    public function test_manual_release_opens_nutrition_before_the_date(): void
    {
        $this->travelTo(Carbon::parse('2026-11-06 12:00:00', ChallengeAccess::TIMEZONE));
        foreach (['alimentacao', 'shopping', 'nutri'] as $feature) {
            $this->mode($feature, 'manual');
        }
        $this->actingAs(User::factory()->create());
        foreach (self::PAGES as $url => $component) {
            $this->get($url)->assertInertia(fn (Assert $page) => $page->component($component));
        }
    }

    public function test_parent_nutrition_block_also_prevents_shopping_writes_and_open_submenus(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 12:00:00', ChallengeAccess::TIMEZONE));
        $this->mode('alimentacao', 'closed');
        $this->mode('shopping', 'manual');
        $this->mode('nutri', 'closed');
        $user = User::factory()->create();
        $user->forceFill(['challenge_early_access' => true])->save();
        $this->actingAs($user);
        foreach (self::PAGES as $url => $component) {
            $this->get($url)->assertInertia(fn (Assert $page) => $page
                ->component('dashboard/EvolutionLocked')
                ->where('evolutionAccess.pages.shopping', false));
        }
        $this->post('/objetivos/alimentacao/compras', ['week' => 1, 'items' => ['Ovos']])->assertForbidden();
        $this->put('/objetivos/alimentacao/compras', ['week' => 1, 'items' => ['Ovos']])->assertForbidden();
    }

    public function test_selected_athlete_early_access_opens_nutrition_only_for_that_athlete(): void
    {
        $this->travelTo(Carbon::parse('2026-11-06 12:00:00', ChallengeAccess::TIMEZONE));
        $selected = User::factory()->create();
        $selected->forceFill(['challenge_early_access' => true])->save();
        foreach (self::PAGES as $url => $component) {
            $this->actingAs($selected)->get($url)->assertInertia(fn (Assert $page) => $page->component($component));
        }
        $this->actingAs(User::factory()->create())->get('/cantinho-da-nutri')
            ->assertInertia(fn (Assert $page) => $page->component('dashboard/EvolutionLocked'));
    }

    public function test_administrator_can_preview_nutrition_while_all_features_are_closed(): void
    {
        $this->travelTo(Carbon::parse('2026-11-06 12:00:00', ChallengeAccess::TIMEZONE));
        foreach (array_keys(ChallengeAccess::FEATURES) as $feature) {
            $this->mode($feature, 'closed');
        }
        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->unpaid()->create());
        foreach (self::PAGES as $url => $component) {
            $this->get($url)->assertInertia(fn (Assert $page) => $page
                ->component($component)
                ->where('evolutionAccess.pages.alimentacao', true)
                ->where('evolutionAccess.pages.nutri', true));
        }
    }
}

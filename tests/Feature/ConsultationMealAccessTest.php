<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ConsultationMealAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_dinner_address_redirects_to_combined_page(): void
    {
        $this->actingAs(User::factory()->create())->get(route('alimentacao.jantar'))
            ->assertRedirect('/alimentacao/refeicoes-reais-em-consultorio');
    }

    private function mode(string $feature, string $mode): void
    {
        DB::table('challenge_access_settings')->updateOrInsert(['feature' => $feature], ['mode' => $mode]);
    }

    public function test_manual_mode_opens_before_seven_days_and_closed_mode_blocks_route_and_menu(): void
    {
        $this->travelTo(Carbon::parse('2026-11-15 12:00:00', 'America/Sao_Paulo'));
        $this->actingAs(User::factory()->create());
        $this->mode('consultation_meals', 'manual');
        $this->get(route('alimentacao.refeicoes-reais'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('ConsultationMeals')->where('consultationMealsUnlocked', true));
        $this->mode('consultation_meals', 'closed');
        $this->get(route('alimentacao.refeicoes-reais'))->assertForbidden();
        $this->get(route('alimentacao.recado-da-nutri'))->assertInertia(fn (Assert $page) => $page->where('consultationMealsUnlocked', false));
        $this->mode('consultation_meals', 'manual');
        $this->mode('alimentacao', 'closed');
        $this->get(route('alimentacao.refeicoes-reais'))->assertInertia(fn (Assert $page) => $page->component('dashboard/EvolutionLocked'));
    }

    public function test_calendar_still_requires_seven_days_and_admin_can_save_new_option(): void
    {
        $this->travelTo(Carbon::parse('2026-11-16 12:00:00', 'America/Sao_Paulo'));
        $athlete = User::factory()->create();
        $athlete->forceFill(['first_challenge_access_at' => now()->subDays(7)])->save();
        $this->actingAs($athlete)->get(route('alimentacao.refeicoes-reais'))->assertOk();
        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->create());
        $this->get(route('admin.challenge-access'))->assertInertia(fn (Assert $page) => $page
            ->where('features.consultation_meals', 'Sugestões de refeições reais - Jantar')
            ->where('modes.consultation_meals', 'calendar'));
        $modes = array_fill_keys(array_keys(\App\Support\ChallengeAccess::FEATURES), 'calendar');
        $modes['consultation_meals'] = 'closed';
        $this->put(route('admin.challenge-access.update'), ['modes' => $modes, 'password' => 'password'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('challenge_access_settings', ['feature' => 'consultation_meals', 'mode' => 'closed']);
        $this->get(route('alimentacao.refeicoes-reais'))->assertOk();
        $this->actingAs($athlete)->get(route('alimentacao.refeicoes-reais'))->assertForbidden();
    }
}

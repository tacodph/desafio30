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

class ReleaseWelcomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_only_appears_after_release_and_is_not_repeated_after_acknowledgement(): void
    {
        $this->travelTo(Carbon::parse('2026-11-07 12:00:00', ChallengeAccess::TIMEZONE));
        $athlete = User::factory()->create(['challenge_paid_at' => now()]);
        $this->actingAs($athlete)->get(route('faq'))->assertInertia(fn (Assert $page) => $page->where('showReleaseWelcome', false));
        $this->post(route('athlete.release-welcome.acknowledge'))->assertForbidden();

        $this->travelTo(Carbon::parse('2026-11-08 20:00:00', ChallengeAccess::TIMEZONE));
        $this->get(route('faq'))->assertInertia(fn (Assert $page) => $page->where('showReleaseWelcome', true));
        $this->from(route('faq'))->post(route('athlete.release-welcome.acknowledge'))->assertRedirect(route('faq'));
        $this->assertNotNull($athlete->fresh()->release_welcome_acknowledged_at);
        $this->get(route('faq'))->assertInertia(fn (Assert $page) => $page->where('showReleaseWelcome', false));
        $firstRead = $athlete->fresh()->release_welcome_acknowledged_at;
        $this->travel(1)->hours();
        $this->post(route('athlete.release-welcome.acknowledge'))->assertRedirect();
        $this->assertTrue($firstRead->equalTo($athlete->fresh()->release_welcome_acknowledged_at));
    }

    public function test_closed_features_prevent_the_welcome_until_released(): void
    {
        $this->travelTo(Carbon::parse('2026-11-09 12:00:00', ChallengeAccess::TIMEZONE));
        $athlete = User::factory()->create(['challenge_paid_at' => now()]);
        DB::table('challenge_access_settings')->updateOrInsert(['feature' => 'nutri'], ['mode' => 'closed']);
        $this->actingAs($athlete)->get(route('faq'))->assertInertia(fn (Assert $page) => $page->where('showReleaseWelcome', false));
        $this->post(route('athlete.release-welcome.acknowledge'))->assertForbidden();
    }

    public function test_unpaid_athletes_and_staff_do_not_receive_the_welcome(): void
    {
        $this->travelTo(Carbon::parse('2026-11-09 12:00:00', ChallengeAccess::TIMEZONE));
        foreach ([UserRole::Atleta, UserRole::Administrador, UserRole::Nutricionista] as $role) {
            $this->actingAs(User::factory()->ofRole($role)->unpaid()->create())
                ->get(route('faq'))->assertInertia(fn (Assert $page) => $page->where('showReleaseWelcome', false));
        }
    }
}

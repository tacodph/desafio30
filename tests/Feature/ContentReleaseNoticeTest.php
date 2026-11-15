<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ContentReleaseNoticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_notice_is_available_after_login_and_shown_only_once_across_logins(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-15 12:00:00', 'America/Sao_Paulo'));
        $athlete = User::factory()->create();
        $this->post('/login', ['email' => $athlete->email, 'password' => 'password'])->assertSessionHas('content_release_notice_pending', true);
        $this->get(route('alimentacao.recado-da-nutri'))->assertInertia(fn (Assert $page) => $page
            ->has('contentReleaseNotice.pages', 2));
        $this->postJson(route('athlete.content-release-notice'))->assertOk()->assertJsonPath('show', true)->assertJsonCount(2, 'pages');
        $this->assertNotNull($athlete->fresh()->content_release_notice_shown_at);
        $this->postJson(route('athlete.content-release-notice'))->assertNotFound();
        $this->post('/logout');
        $this->post('/login', ['email' => $athlete->email, 'password' => 'password']);
        $this->get(route('alimentacao.recado-da-nutri'))->assertInertia(fn (Assert $page) => $page->where('contentReleaseNotice', null));
    }

    public function test_notice_only_lists_pages_the_athlete_can_open(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-15 12:00:00', 'America/Sao_Paulo'));
        DB::table('challenge_access_settings')->updateOrInsert(['feature' => 'consultation_meals'], ['mode' => 'closed']);
        $this->actingAs(User::factory()->create())->withSession(['content_release_notice_pending' => true])
            ->postJson(route('athlete.content-release-notice'))->assertOk()->assertJsonCount(1, 'pages')
            ->assertJsonPath('pages.0.href', '/cantinho-da-nutri');
    }

    public function test_hidden_pages_do_not_consume_the_notice_and_staff_cannot_claim_it(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-15 12:00:00', 'America/Sao_Paulo'));
        foreach (['alimentacao', 'nutri'] as $feature) {
            DB::table('challenge_access_settings')->updateOrInsert(['feature' => $feature], ['mode' => 'closed']);
        }
        $athlete = User::factory()->create();
        $this->actingAs($athlete)->withSession(['content_release_notice_pending' => true])
            ->postJson(route('athlete.content-release-notice'))->assertNotFound();
        $this->assertNull($athlete->fresh()->content_release_notice_shown_at);
        $this->actingAs(User::factory()->ofRole(UserRole::Nutricionista)->create())
            ->postJson(route('athlete.content-release-notice'))->assertForbidden();
    }

    public function test_notice_requires_a_login_session_and_release_date(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-14 23:59:59', 'America/Sao_Paulo'));
        $this->actingAs(User::factory()->create())->withSession(['content_release_notice_pending' => true])
            ->postJson(route('athlete.content-release-notice'))->assertNotFound();
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-15 00:00:00', 'America/Sao_Paulo'));
        $this->withSession(['content_release_notice_pending' => false])
            ->postJson(route('athlete.content-release-notice'))->assertNotFound();
    }
}

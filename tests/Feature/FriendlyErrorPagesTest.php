<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FriendlyErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_page_has_recovery_actions(): void
    {
        $this->get('/pagina-que-nao-existe')
            ->assertNotFound()
            ->assertSee('Esse caminho não foi encontrado')
            ->assertSee('Falar com o suporte');
    }

    public function test_expired_session_has_a_login_link(): void
    {
        Route::middleware('web')->get('/test-expired', fn () => abort(419));

        $this->get('/test-expired')->assertStatus(419)
            ->assertSee('Sua sessão expirou')
            ->assertSee('href="/login"', false);
    }

    public function test_inertia_errors_render_a_page_instead_of_an_html_modal(): void
    {
        Route::middleware('web')->get('/test-expired', fn () => abort(419));

        $this->get('/test-expired', ['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->assertStatus(419)
            ->assertHeader('X-Inertia', 'true')
            ->assertInertia(fn (Assert $page) => $page->component('Error')
                ->where('status', 419)->where('href', '/login'));
    }

    public function test_json_errors_keep_their_original_format(): void
    {
        $this->getJson('/pagina-que-nao-existe')->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    public function test_rate_limit_keeps_the_retry_header(): void
    {
        Route::get('/test-rate-limit', fn () => abort(429, '', ['Retry-After' => 60]));

        $this->get('/test-rate-limit')->assertStatus(429)
            ->assertHeader('Retry-After', '60')->assertSee('pequena pausa');
    }
}

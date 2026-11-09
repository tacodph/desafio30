<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirects_to_accounts(): void
    {
        config([
            'app.url' => 'https://desafio45.test',
            'services.google.client_id' => 'client-id',
            'services.google.client_secret' => 'client-secret',
        ]);

        $response = $this->get(route('auth.google'));

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('https://accounts.google.com/o/oauth2/v2/auth', $location);
        $this->assertStringContainsString(rawurlencode('https://desafio45.test/auth/google/callback'), $location);
    }

    public function test_google_callback_registers_and_logs_in(): void
    {
        $this->travelTo(Carbon::parse('2026-11-09 09:59:59', 'America/Sao_Paulo'));

        $this->fakeGoogle([
            'sub' => 123456789012345678,
            'email' => 'ana@gmail.com',
            'email_verified' => true,
            'name' => 'Ana',
        ]);

        $response = $this->withSession(['google_oauth_state' => 'state-1'])
            ->get('/auth/google/callback?state=state-1&code=abc');

        $response->assertRedirect(route('pagamento.show', absolute: false));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'ana@gmail.com',
            'google_id' => '123456789012345678',
            'name' => 'Ana',
        ]);
        $this->assertSame('atleta', User::where('email', 'ana@gmail.com')->first()->userType->name);
    }

    public function test_google_callback_links_an_existing_email(): void
    {
        $this->travelTo(Carbon::parse('2026-11-09 10:00:00', 'America/Sao_Paulo'));

        $user = User::factory()->create(['email' => 'ana@gmail.com']);

        $this->fakeGoogle([
            'sub' => 'google-1',
            'email' => 'ana@gmail.com',
            'email_verified' => true,
            'name' => 'Ana',
        ]);

        $this->withSession(['google_oauth_state' => 'state-1'])
            ->get('/auth/google/callback?state=state-1&code=abc')
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-1', $user->fresh()->google_id);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        $this->fakeGoogle([
            'sub' => 'google-1',
            'email' => 'ana@gmail.com',
            'email_verified' => false,
            'name' => 'Ana',
        ]);

        $this->withSession(['google_oauth_state' => 'state-1'])
            ->get('/auth/google/callback?state=state-1&code=abc')
            ->assertForbidden();

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private function fakeGoogle(array $profile): void
    {
        config([
            'services.google.client_id' => 'client-id',
            'services.google.client_secret' => 'client-secret',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'token']),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response($profile),
        ]);
    }
}

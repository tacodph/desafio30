<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-11-09 09:59:59', 'America/Sao_Paulo'));
    }

    public function test_registration_closes_at_ten_in_brasilia(): void
    {
        $this->travel(1)->second();

        $this->get('/register')->assertRedirect(route('login'));
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'late@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('users', ['email' => 'late@example.com']);
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('pagamento.show', absolute: false));
        $this->assertDatabaseCount('user_types', 5);
        $this->assertSame('atleta', User::where('email', 'test@example.com')->first()->userType->name);
    }

    public function test_public_register_cannot_choose_another_role()
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type_id' => 1,
        ]);

        $this->assertSame('atleta', User::where('email', 'test@example.com')->first()->userType->name);
    }
}

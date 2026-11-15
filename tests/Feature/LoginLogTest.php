<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_logins_record_user_and_time_for_all_roles(): void
    {
        $this->freezeSecond();
        foreach ([UserRole::Atleta, UserRole::Nutricionista, UserRole::Administrador] as $role) {
            $user = User::factory()->ofRole($role)->create();
            $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();
            $this->assertDatabaseHas('login_logs', ['user_id' => $user->id, 'logged_in_at' => now()->toDateTimeString()]);
            $this->post('/logout');
            $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect();
            $this->assertSame(2, \Illuminate\Support\Facades\DB::table('login_logs')->where('user_id', $user->id)->count());
            $this->post('/logout');
        }
    }

    public function test_failed_login_does_not_create_log(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'incorrect'])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('login_logs', 0);
    }
}

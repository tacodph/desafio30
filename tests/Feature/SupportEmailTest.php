<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SupportEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_prefills_the_registered_email(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/suporte')->assertInertia(fn (Assert $page) => $page
            ->component('Suporte')->where('accountEmail', $user->email)->where('accountName', $user->name));
    }

    public function test_authenticated_sender_cannot_replace_the_registered_email(): void
    {
        config(['mail.default' => 'array', 'support.email' => 'support@example.com']);
        $user = User::factory()->create();
        $this->actingAs($user)->post('/suporte', ['name' => 'Atleta', 'email' => 'outro@example.com', 'subject' => 'Dúvida', 'message' => 'Preciso de ajuda.'])
            ->assertSessionHasNoErrors()->assertRedirect('/suporte');
        $mail = Mail::mailer('array')->getSymfonyTransport()->messages()->first()->getOriginalMessage();
        $this->assertSame($user->email, $mail->getReplyTo()[0]->getAddress());
        $this->assertStringContainsString('Email: '.$user->email, $mail->getTextBody());
        $this->assertStringNotContainsString('outro@example.com', $mail->getTextBody());
    }

    public function test_guest_can_still_provide_a_contact_email(): void
    {
        config(['mail.default' => 'array', 'support.email' => 'support@example.com']);
        $this->get('/suporte')->assertInertia(fn (Assert $page) => $page->where('accountEmail', null));
        $this->post('/suporte', ['name' => 'Visitante', 'email' => 'visitante@example.com', 'subject' => 'Dúvida', 'message' => 'Preciso de ajuda.'])
            ->assertSessionHasNoErrors()->assertRedirect('/suporte');
        $mail = Mail::mailer('array')->getSymfonyTransport()->messages()->first()->getOriginalMessage();
        $this->assertSame('visitante@example.com', $mail->getReplyTo()[0]->getAddress());
    }

    public function test_missing_recipient_shows_a_configuration_error_instead_of_success(): void
    {
        config(['mail.default' => 'array', 'support.email' => null]);
        $this->post('/suporte', ['name' => 'Visitante', 'email' => 'visitante@example.com', 'subject' => 'Dúvida', 'message' => 'Preciso de ajuda.'])
            ->assertSessionHasErrors(['message' => 'O endereço de email do suporte não foi configurado no servidor. Avise a administração para verificar SUPPORT_EMAIL e o cache de configuração.'])
            ->assertSessionMissing('status');
        $this->assertDatabaseCount('email_logs', 0);
    }
}

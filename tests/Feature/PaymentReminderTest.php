<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Jobs\SendPendingPaymentReminder;
use App\Models\ChallengePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PaymentReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_queue_an_edited_reminder_only_for_selected_pending_athletes(): void
    {
        Bus::fake();
        $admin = User::factory()->ofRole(UserRole::Administrador)->create();
        $pending = User::factory()->unpaid()->create();
        $notSelected = User::factory()->unpaid()->create();
        $paid = User::factory()->create();
        $courtesy = User::factory()->unpaid()->create();
        $courtesy->forceFill(['challenge_courtesy_at' => now()])->save();
        User::factory()->unpaid()->create();

        foreach ([$pending, $notSelected, $paid, $courtesy] as $athlete) {
            ChallengePayment::create([
                'user_id' => $athlete->id,
                'asaas_payment_id' => 'pay_'.$athlete->id,
                'status' => ChallengePayment::PENDING,
                'value' => 79.90,
                'due_date' => today()->addDays(2),
            ]);
        }

        $data = ['athlete_ids' => [$pending->id], 'subject' => 'Assunto editado', 'body' => 'Mensagem editada.'];

        $this->actingAs(User::factory()->ofRole(UserRole::Nutricionista)->create())
            ->get(route('admin.payment-reminders'))->assertForbidden();
        $this->post(route('admin.payment-reminders.store'), $data)->assertForbidden();

        $this->actingAs($admin)->get(route('admin.payment-reminders'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard/PaymentReminders')
                ->has('athletes', 2)
                ->where('defaultSubject', 'Ei!!! Te vejo segunda no Desafio da Virada?'));

        $this->post(route('admin.payment-reminders.store'), [...$data, 'athlete_ids' => [$pending->id, $paid->id]])
            ->assertSessionHasErrors('athlete_ids');
        Bus::assertNothingDispatched();

        $this->post(route('admin.payment-reminders.store'), $data)
            ->assertRedirect(route('admin.payment-reminders'))
            ->assertSessionHas('payment_reminders_queued', 1);

        Bus::assertDispatchedTimes(SendPendingPaymentReminder::class, 1);
        Bus::assertDispatched(SendPendingPaymentReminder::class, fn ($job) => $job->athleteId === $pending->id
            && $job->subject === $data['subject']
            && $job->body === $data['body']);
    }
}

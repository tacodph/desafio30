<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Jobs\SendPendingPaymentReminder;
use App\Models\User;
use App\Models\EmailLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PaymentReminderController extends Controller
{
    public function show(Request $request): Response
    {
        abort_unless($request->user()->userType?->name === UserRole::Administrador->value, 403);

        return Inertia::render('dashboard/PaymentReminders', [
            'athletes' => User::query()->withPendingChallengePayment()->orderBy('name')->orderBy('id')->get(['id', 'name', 'email']),
            'defaultSubject' => 'Ei!!! Te vejo segunda no Desafio da Virada?',
            'defaultBody' => "Oiii, atleta! 🎉\n\n"
                ."Você já deu o primeiro passo ao fazer sua inscrição para o Desafio da Virada.\n\n"
                ."Agora só falta concluir o pagamento para garantir sua vaga!\n\n"
                ."Vamos passar 30 dias juntos, nos motivando, colocando planos em prática e transformando pequenas escolhas em hábitos que queremos levar para muito além desses 30 dias.\n\n"
                ."Afinal, 2027 está chegando e a ideia é entrar no novo ano com mais disposição, organização e a sensação de que começamos a fazer por nós aquilo que tanto adiamos.\n\n"
                ."Te vejo segunda!!🚀✨",
            'queued' => $request->session()->get('payment_reminders_queued'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->userType?->name === UserRole::Administrador->value, 403);

        $data = $request->validate([
            'athlete_ids' => ['required', 'array', 'min:1'],
            'athlete_ids.*' => ['required', 'integer', 'distinct'],
            'subject' => ['required', 'string', 'max:180', 'not_regex:/[\r\n]/'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $eligibleIds = User::query()->withPendingChallengePayment()->whereIn('id', $data['athlete_ids'])->pluck('id');

        if ($eligibleIds->count() !== count($data['athlete_ids'])) {
            throw ValidationException::withMessages(['athlete_ids' => 'A lista de pagamentos pendentes mudou. Confira os atletas selecionados antes de enviar.']);
        }

        DB::transaction(function () use ($eligibleIds, $data, $request) {
            foreach ($eligibleIds as $id) {
                $athlete = User::findOrFail($id);
                $log = EmailLog::create([
                    'actor_id' => $request->user()->id, 'category' => 'payment_reminder',
                    'recipient_email' => $athlete->email,
                    'recipients' => ['to' => [['email' => $athlete->email, 'name' => $athlete->name]]],
                    'subject' => $data['subject'], 'body_text' => $data['body'], 'status' => 'queued',
                ]);
                SendPendingPaymentReminder::dispatch($id, $data['subject'], $data['body'], $log->id)->afterCommit();
            }
        });

        return to_route('admin.payment-reminders')->with('payment_reminders_queued', $eligibleIds->count());
    }
}

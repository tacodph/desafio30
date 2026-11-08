<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Models\EmailLog;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function show(Request $request)
    {
        return Inertia::render('Suporte', [
            'contactEmail' => config('support.email'),
            'accountEmail' => $request->user()?->email,
            'accountName' => $request->user()?->name,
            'status' => $request->session()->get('status'),
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()) {
            $request->merge(['email' => $request->user()->email]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:254'],
            'subject' => ['required', 'string', 'max:150', 'not_regex:/[\r\n]/'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if (! config('support.email')) {
            throw ValidationException::withMessages(['message' => 'O endereço de email do suporte não foi configurado no servidor. Avise a administração para verificar SUPPORT_EMAIL e o cache de configuração.']);
        }

        $body = "Nome: {$data['name']}\nEmail: {$data['email']}\n\n{$data['message']}";
        $log = EmailLog::create([
            'actor_id' => $request->user()?->id, 'category' => 'support',
            'recipient_email' => config('support.email'), 'subject' => '[Suporte] '.$data['subject'],
            'body_text' => $body, 'status' => 'queued',
        ]);

        try {
            Mail::raw($body, function (Message $message) use ($data, $log) {
                $message->to(config('support.email'))
                    ->replyTo($data['email'])
                    ->subject('[Suporte] '.$data['subject']);
                $message->getHeaders()->addTextHeader('X-Desafio-Category', 'support');
                $message->getHeaders()->addTextHeader('X-Desafio-Email-Log', (string) $log->id);
            });
        } catch (\Throwable $exception) {
            if (in_array($log->fresh()->status, ['queued', 'sending'], true)) {
                $log->update(['status' => 'failed', 'error' => 'Não foi possível iniciar o envio. Confira a configuração do serviço de email e os logs da aplicação.']);
            }
            report($exception);

            throw ValidationException::withMessages(['message' => 'Não foi possível enviar sua mensagem. Tente novamente ou use o email de contato.']);
        }

        $status = $log->fresh()->status === 'logged'
            ? 'Mensagem registrada. O serviço de email está em modo de teste e não realizou envio externo.'
            : 'Mensagem enviada! Nossa equipe responderá pelo email informado.';

        return to_route('suporte')->with('status', $status);
    }
}

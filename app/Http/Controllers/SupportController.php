<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function show(Request $request)
    {
        return Inertia::render('Suporte', [
            'contactEmail' => config('support.email'),
            'status' => $request->session()->get('status'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:254'],
            'subject' => ['required', 'string', 'max:150', 'not_regex:/[\r\n]/'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if (! config('support.email')) {
            throw ValidationException::withMessages(['message' => 'O suporte ainda não está disponível. Tente novamente mais tarde.']);
        }

        try {
            Mail::raw("Nome: {$data['name']}\nEmail: {$data['email']}\n\n{$data['message']}", function (Message $message) use ($data) {
                $message->to(config('support.email'))
                    ->replyTo($data['email'])
                    ->subject('[Suporte] '.$data['subject']);
            });
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages(['message' => 'Não foi possível enviar sua mensagem. Tente novamente ou use o email de contato.']);
        }

        return to_route('suporte')->with('status', 'Mensagem enviada! Nossa equipe responderá pelo email informado.');
    }
}

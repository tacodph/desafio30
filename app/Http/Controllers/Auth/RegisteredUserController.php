<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserType;
use App\Support\ChallengeAccess;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        if (ChallengeAccess::registrationIsClosed()) {
            return to_route('login')->with('status', 'As inscrições para o Desafio da Virada foram encerradas.');
        }

        return Inertia::render('auth/Register', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (ChallengeAccess::registrationIsClosed()) {
            return to_route('login')->with('status', 'As inscrições para o Desafio da Virada foram encerradas.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type_id' => UserType::idFor(UserRole::Atleta),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Atleta novo ainda não pagou: o primeiro acesso é a página de pagamento.
        return to_route('pagamento.show');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserType;
use App\Support\ChallengeAccess;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return back()->with('status', 'Login com Gmail ainda não está configurado.');
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        $expected = $request->session()->pull('google_oauth_state');
        $given = $request->query('state');

        if (! is_string($expected) || ! is_string($given) || ! hash_equals($expected, $given)) {
            abort(403);
        }

        if ($request->filled('error') || ! is_string($request->query('code'))) {
            return to_route('login')->with('status', 'Não foi possível entrar com o Gmail.');
        }

        try {
            $token = Http::asForm()->acceptJson()->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'code' => $request->query('code'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri(),
            ])->throw()->json();

            $profile = Http::withToken($token['access_token'] ?? '')
                ->acceptJson()
                ->get('https://www.googleapis.com/oauth2/v3/userinfo')
                ->throw()
                ->json();
        } catch (RequestException) {
            return to_route('login')->with('status', 'Não foi possível entrar com o Gmail.');
        }

        $email = strtolower((string) ($profile['email'] ?? ''));
        $verified = filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $googleId = isset($profile['sub']) ? (string) $profile['sub'] : '';

        if (! $verified || $googleId === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            abort(403);
        }

        $user = User::query()->where('google_id', $googleId)->first()
            ?? User::query()->whereRaw('lower(email) = ?', [$email])->first();

        if ($user?->google_id && $user->google_id !== $googleId) {
            return to_route('login')->with('status', 'Este e-mail já está ligado a outra conta Google.');
        }

        if ($user) {
            $user->forceFill([
                'google_id' => $googleId,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            if (ChallengeAccess::registrationIsClosed()) {
                return to_route('login')->with('status', 'As inscrições para o Desafio da Virada foram encerradas.');
            }

            $name = trim((string) ($profile['name'] ?? ''));

            $user = User::create([
                'name' => Str::limit($name !== '' ? $name : Str::before($email, '@'), 255, ''),
                'email' => $email,
                'google_id' => $googleId,
                'password' => Str::random(40),
                'user_type_id' => UserType::idFor(UserRole::Atleta),
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();

            event(new Registered($user));
        }

        Auth::login($user, true);

        $request->session()->regenerate();

        if ($user->isAthlete() && ! $user->hasChallengeAccess()) {
            return to_route('pagamento.show');
        }

        return to_route('dashboard');
    }

    private function redirectUri(): string
    {
        return rtrim((string) config('app.url'), '/').'/auth/google/callback';
    }
}

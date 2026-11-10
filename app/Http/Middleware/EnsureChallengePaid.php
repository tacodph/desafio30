<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChallengePaid
{
    /**
     * Atleta que ainda não pagou só enxerga a página de pagamento. A equipe passa direto.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isAthlete()) {
            if (! $user->hasChallengeAccess()) {
                return redirect()->route('pagamento.show');
            }

            if ($user->first_challenge_access_at === null) {
                $user->newQuery()->whereKey($user->id)->whereNull('first_challenge_access_at')
                    ->update(['first_challenge_access_at' => now()]);
                $user->first_challenge_access_at = $user->newQuery()->whereKey($user->id)->value('first_challenge_access_at');
            }
        }

        return $next($request);
    }
}

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

        if ($user && $user->isAthlete() && ! $user->hasChallengeAccess()) {
            return redirect()->route('pagamento.show');
        }

        return $next($request);
    }
}

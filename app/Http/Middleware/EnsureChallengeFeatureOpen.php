<?php

namespace App\Http\Middleware;

use App\Support\ChallengeAccess;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureChallengeFeatureOpen
{
    public function __construct(private readonly ChallengeAccess $access) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if ($request->user()?->isAthlete() && ! $this->access->pageOpen($feature)) {
            return Inertia::render('dashboard/EvolutionLocked', [
                'feature' => ChallengeAccess::FEATURES[$feature] ?? 'Conteúdo do desafio',
                'access' => $this->access->payload(),
            ])->toResponse($request);
        }

        return $next($request);
    }
}

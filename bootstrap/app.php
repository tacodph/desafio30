<?php

use App\Http\Middleware\EnsureChallengePaid;
use App\Http\Middleware\EnsureChallengeFeatureOpen;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'challenge.paid' => EnsureChallengePaid::class,
            'challenge.feature' => EnsureChallengeFeatureOpen::class,
        ]);

        // O webhook do Asaas é server-to-server e se autentica pelo header asaas-access-token.
        $middleware->validateCsrfTokens(except: ['webhooks/asaas']);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

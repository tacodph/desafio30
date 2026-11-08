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
        $exceptions->respond(function ($response, $exception, $request) {
            $status = $response->getStatusCode();

            if (! in_array($status, [401, 403, 404, 419, 429, 500, 502, 503, 504], true)
                || ($request->expectsJson() && ! $request->header('X-Inertia'))
                || (config('app.debug') && $status >= 500)) {
                return $response;
            }

            $content = \App\Support\ErrorPage::content($status);
            $headers = array_intersect_key($response->headers->all(), array_flip(['retry-after', 'allow']));
            $headers['Cache-Control'] = 'no-store, private';

            if ($request->header('X-Inertia')) {
                $page = \Inertia\Inertia::render('Error', $content)->toResponse($request);
                $page->setStatusCode($status);
                $page->headers->add($headers);

                return $page;
            }

            return response()->view('errors.friendly', $content, $status, $headers);
        });
    })->create();

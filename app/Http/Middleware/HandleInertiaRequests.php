<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use App\Support\ChallengeAccess;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return array_merge(parent::share($request), [
            ...parent::share($request),
            'evolutionAccess' => function () use ($request) {
                if ($request->user()?->userType?->name === 'administrador') {
                    return ['releaseDate' => (new ChallengeAccess)->releaseDate()->format('d/m/Y'),
                        'pages' => array_fill_keys(ChallengeAccess::PAGE_FEATURES, true)];
                }
                if (! $request->user()?->isAthlete()) {
                    return null;
                }
                $access = new ChallengeAccess;

                return [
                    'releaseDate' => $access->releaseDate()->format('d/m/Y'),
                    'pages' => collect(ChallengeAccess::PAGE_FEATURES)->mapWithKeys(
                        fn (string $tab) => [$tab => $access->pageOpen($tab)],
                    )->all(),
                ];
            },
            'accessNotice' => fn () => $request->session()->get('access_notice'),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user()?->loadMissing('userType:id,name'),
            ],
        ]);
    }
}

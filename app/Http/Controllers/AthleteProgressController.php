<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Achievement;
use App\Models\AthleteProfile;
use App\Models\Checkin;
use App\Models\FoodGroup;
use App\Models\User;
use App\Models\UserType;
use App\Support\ChallengeSheet;
use App\Support\ChallengeAccess;
use App\Support\DayLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Throwable;

class AthleteProgressController extends Controller
{
    private const START_PHOTOS = ['photo_front' => 'foto de frente', 'photo_side' => 'foto de lado', 'photo_back' => 'foto de costas'];

    public function __construct(private readonly ChallengeAccess $access) {}

    public function welcome(Request $request): Response|RedirectResponse
    {
        $this->abortUnlessAthlete($request);

        return $request->user()->welcome_acknowledged_at
            ? to_route('dashboard')
            : Inertia::render('dashboard/BoasVindas');
    }

    public function acknowledgeWelcome(Request $request): RedirectResponse
    {
        $this->abortUnlessAthlete($request);

        User::query()
            ->whereKey($request->user()->id)
            ->whereNull('welcome_acknowledged_at')
            ->update(['welcome_acknowledged_at' => now()]);

        return to_route('dashboard');
    }

    public function firstDay(Request $request): Response|RedirectResponse
    {
        $this->abortUnlessAthlete($request);

        if (! $request->user()->welcome_acknowledged_at) {
            return to_route('athlete.welcome');
        }

        $profile = $request->user()->athleteProfile;

        return Inertia::render('dashboard/PrimeiroDia', [
            'firstAccess' => ! $profile?->welcomed_at,
            'baseline' => [
                'weight' => $profile?->weight ?? '',
                'waist' => $profile?->waist ?? '',
                'abdomen' => $profile?->abdomen ?? '',
                'hip' => $profile?->hip ?? '',
                'photo' => $profile?->photo ? '/storage/'.$profile->photo : null,
                'startPhotos' => array_map(fn (string $path) => Storage::disk('public')->url($path), $profile?->start_photos ?? []),
            ],
        ]);
    }

    public function storeFirstDay(Request $request): RedirectResponse
    {
        $this->abortUnlessAthlete($request);

        if (! $request->user()->welcome_acknowledged_at) {
            return to_route('athlete.welcome');
        }

        $disk = Storage::disk('public');
        $newPhotos = [];
        $replacedPhotos = [];

        try {
            $profile = DB::transaction(function () use ($request, $disk, &$newPhotos, &$replacedPhotos) {
                $userId = $request->user()->id;
                User::query()->lockForUpdate()->findOrFail($userId);
                $profile = AthleteProfile::query()->firstOrNew(['user_id' => $userId]);
                $photos = $profile->start_photos ?? [];
                $rules = [
                    'weight' => 'nullable|numeric|between:30,300',
                    'waist' => 'nullable|numeric|between:40,200',
                    'abdomen' => 'nullable|numeric|between:40,200',
                    'hip' => 'nullable|numeric|between:40,200',
                ];

                foreach (self::START_PHOTOS as $key => $label) {
                    $rules[$key] = [Rule::requiredIf(empty($photos[$key])), 'nullable', 'image', 'max:5120'];
                }

                $data = $request->validate($rules, [], self::START_PHOTOS);

                foreach (array_keys(self::START_PHOTOS) as $key) {
                    if (! isset($data[$key])) {
                        continue;
                    }

                    $path = $disk->putFile('baselines', $data[$key]);
                    if ($path === false) {
                        throw new RuntimeException('Não foi possível salvar a foto.');
                    }

                    $newPhotos[] = $path;
                    if (isset($photos[$key])) {
                        $replacedPhotos[] = $photos[$key];
                    }
                    $photos[$key] = $path;
                }

                $profile->fill([
                    'weight' => $data['weight'] ?? null,
                    'waist' => $data['waist'] ?? null,
                    'abdomen' => $data['abdomen'] ?? null,
                    'hip' => $data['hip'] ?? null,
                    'welcomed_at' => $profile->welcomed_at ?? now(),
                ]);
                $profile->start_photos = $photos;
                $profile->save();

                return $profile;
            });
        } catch (Throwable $error) {
            $disk->delete($newPhotos);

            throw $error;
        }

        $disk->delete($replacedPhotos);

        // No primeiro acesso, o segundo passo é preencher as metas.
        return $profile->goals_set_at ? to_route('athlete.start.first-day') : to_route('athlete.start.goals');
    }

    public function goals(Request $request): Response
    {
        $this->abortUnlessAthlete($request);

        $payload = ChallengeSheet::payload($request->user());

        return Inertia::render('dashboard/Metas', [
            'firstAccess' => ! $request->user()->athleteProfile?->goals_set_at,
            'profile' => $payload['profile'],
            'penalty' => $payload['penalty'],
            'weight' => $request->user()->athleteProfile?->weight,
            'waterMinimum' => DayLog::waterMinimumLiters($request->user()->athleteProfile?->weight),
        ]);
    }
    public function show(Request $request, string $tab): Response|RedirectResponse
    {
        if ($request->user()->userType?->name === UserRole::Administrador->value) {
            return $this->page($request->user(), false, false, $tab, true);
        }

        $this->abortUnlessAthlete($request);

        if ($pending = $this->pendingStart($request)) {
            return $pending;
        }

        if (! $this->access->pageOpen($tab)) {
            return to_route('dashboard')->with('access_notice', 'Esta página será liberada a partir de '.$this->access->releaseDate()->format('d/m/Y').', conforme as liberações da administração.');
        }

        return $this->page($request->user(), true, false, $tab);
    }

    public function showAthlete(Request $request, User $athlete): Response
    {
        abort_unless(UserRole::from($request->user()->userType->name) === UserRole::Nutricionista, 403);
        abort_unless($athlete->userType->name === UserRole::Atleta->value, 404);

        return $this->page($athlete, false, true);
    }

    public function updateGoals(Request $request): RedirectResponse
    {
        $this->abortUnlessAthlete($request);

        $data = $request->validate([
            'movement_level' => 'nullable|in:iniciante,intermediario,avancado',
            'movement_minutes' => 'nullable|integer|min:0|max:300',
            'water_liters' => 'nullable|numeric|min:0|max:15',
            'sleep_goal' => 'nullable|string|max:255',
            'skill' => 'nullable|string|max:255',
            'strength_goal' => 'nullable|string|max:255',
            'test_1' => 'nullable|string|max:255',
            'test_15' => 'nullable|string|max:255',
            'test_30' => 'nullable|string|max:255',
            'test_45' => 'nullable|string|max:255',
            'penalty' => 'boolean',
            'penalty_donated_on' => 'nullable|date',
            'penalty_note' => 'nullable|string|max:255',
        ]);

        $profile = AthleteProfile::query()->firstOrNew(['user_id' => $request->user()->id]);
        $firstTime = $profile->goals_set_at === null;

        $profile->fill([...$data, 'goals_set_at' => $profile->goals_set_at ?? now()])->save();

        return $firstTime ? to_route('dashboard') : to_route('athlete.start.goals');
    }

    public function updateSkills(Request $request): RedirectResponse
    {
        $this->abortUnlessAthlete($request);

        if ($pending = $this->pendingStart($request)) {
            return $pending;
        }

        abort_unless($this->access->pageOpen('dias'), 403, 'A página de dias está bloqueada.');

        $data = $request->validate([
            'week' => 'required|integer|between:1,'.count(ChallengeSheet::WEEKS),
            'picks' => 'required|array|size:1',
            'picks.*' => 'required|string|max:180',
        ]);

        $profile = AthleteProfile::query()->firstOrNew(['user_id' => $request->user()->id]);
        $weeks = array_replace(array_fill(0, count(ChallengeSheet::WEEKS), []), array_values($profile->weekly_skills ?? []));
        $weeks[$data['week'] - 1] = array_values($data['picks']);

        $profile->fill(['weekly_skills' => array_values($weeks)])->save();

        return to_route('athlete.progress.tab', 'dias');
    }

    public function updateDays(Request $request): RedirectResponse
    {
        $this->abortUnlessAthlete($request);

        if ($pending = $this->pendingStart($request)) {
            return $pending;
        }

        $data = $request->validate([
            'days' => 'required|array|max:30',
            'days.*.day' => 'required|integer|between:1,30|distinct',
            'days.*.checked_in' => 'boolean',
            'days.*.movement' => 'boolean',
            'days.*.water' => 'boolean',
            'days.*.sleep' => 'boolean',
            'days.*.strength' => 'boolean',
            'days.*.skill' => 'boolean',
            'days.*.plus_count' => 'integer|min:0|max:9',
            'days.*.notes' => 'nullable|array',
            'days.*.notes.*' => 'nullable|string|max:255',
            'days.*.details' => 'nullable|array',
            'days.*.details.workouts' => 'required_with:days.*.details|array|min:1|max:6',
            'days.*.details.workouts.*.type' => 'required|string|max:40',
            'days.*.details.workouts.*.minutes' => 'nullable|integer|min:0|max:600',
            'days.*.details.water_ml' => 'nullable|integer|min:0|max:10000',
            'days.*.details.sleep_hours' => 'nullable|numeric|min:0|max:24',
            'days.*.details.food' => 'nullable|array',
            'days.*.details.food.*' => 'boolean',
            'days.*.details.skill' => 'nullable|string|max:180',
            'days.*.details.manual' => 'nullable|array',
            'days.*.details.manual.*' => 'boolean',
        ]);

        foreach ($data['days'] as $day) {
            $this->access->ensureDayEditable((int) $day['day']);
        }

        $profile = $request->user()->athleteProfile;

        foreach ($data['days'] as $day) {
            $details = isset($day['details']) ? DayLog::normalize($day['details']) : null;
            $achieved = $details ? DayLog::achieved($details, $profile) : [
                'movement' => $day['movement'] ?? false,
                'water' => $day['water'] ?? false,
                'sleep' => $day['sleep'] ?? false,
                'strength' => $day['strength'] ?? false,
                'skill' => $day['skill'] ?? false,
            ];

            Checkin::query()->updateOrCreate(
                ['user_id' => $request->user()->id, 'day' => $day['day']],
                [
                    'checked_in' => true,
                    ...$achieved,
                    'details' => $details,
                    'plus_count' => $day['plus_count'] ?? 0,
                    'notes' => collect($day['notes'] ?? [])
                        ->only(['movement', 'water', 'sleep', 'strength', 'skill', 'plus', 'day'])
                        ->map(fn ($note) => filled($note) ? $note : null)
                        ->filter()
                        ->all() ?: null,
                ],
            );
        }

        return to_route('athlete.progress.tab', 'dias');
    }

    public function storeWorkoutPhoto(Request $request, int $day): RedirectResponse
    {
        $this->abortUnlessAthlete($request);

        if ($pending = $this->pendingStart($request)) {
            return $pending;
        }

        abort_unless($day >= 1 && $day <= ChallengeSheet::TOTAL_DAYS, 404);

        $this->access->ensureDayEditable($day);

        $data = $request->validate(['photo' => 'required|image|max:5120']);

        $checkin = Checkin::query()->firstOrNew(['user_id' => $request->user()->id, 'day' => $day]);

        if ($checkin->workout_photo) {
            Storage::disk('public')->delete($checkin->workout_photo);
        }

        $checkin->workout_photo = $data['photo']->store('workouts', 'public');
        $checkin->save();

        return back();
    }

    public function destroyWorkoutPhoto(Request $request, int $day): RedirectResponse
    {
        $this->abortUnlessAthlete($request);

        if ($pending = $this->pendingStart($request)) {
            return $pending;
        }

        $this->access->ensureDayEditable($day);

        $checkin = Checkin::query()->where(['user_id' => $request->user()->id, 'day' => $day])->first();

        if ($checkin?->workout_photo) {
            Storage::disk('public')->delete($checkin->workout_photo);
            $checkin->update(['workout_photo' => null]);
        }

        return back();
    }

    public function storeMealPhoto(Request $request, int $day): RedirectResponse
    {
        $this->abortUnlessAthlete($request);

        if ($pending = $this->pendingStart($request)) {
            return $pending;
        }

        abort_unless($day >= 1 && $day <= ChallengeSheet::TOTAL_DAYS, 404);

        $this->access->ensureDayEditable($day);

        $data = $request->validate(['photo' => 'required|image|max:5120']);
        $disk = Storage::disk('public');
        $path = $disk->putFile('meals', $data['photo']);

        if ($path === false) {
            throw ValidationException::withMessages(['photo' => 'Não foi possível salvar a foto da refeição.']);
        }

        try {
            DB::transaction(function () use ($request, $day, $path) {
                $userId = $request->user()->id;
                User::query()->lockForUpdate()->findOrFail($userId);
                $checkin = Checkin::query()->firstOrNew(['user_id' => $userId, 'day' => $day]);
                $checkin->meal_photos = [...($checkin->meal_photos ?? []), $path];
                $checkin->save();
            });
        } catch (Throwable $error) {
            $disk->delete($path);

            throw $error;
        }

        return back();
    }

    public function destroyMealPhoto(Request $request, int $day, ?string $photo = null): RedirectResponse
    {
        $this->abortUnlessAthlete($request);

        if ($pending = $this->pendingStart($request)) {
            return $pending;
        }

        $this->access->ensureDayEditable($day);

        $removed = DB::transaction(function () use ($request, $day, $photo) {
            $userId = $request->user()->id;
            User::query()->lockForUpdate()->findOrFail($userId);
            $checkin = Checkin::query()->where(['user_id' => $userId, 'day' => $day])->first();

            if (! $checkin) {
                return null;
            }

            if ($checkin->meal_photo && ($photo === null || basename($checkin->meal_photo) === $photo)) {
                $path = $checkin->meal_photo;
                $checkin->update(['meal_photo' => null]);

                return $path;
            }

            $photos = $checkin->meal_photos ?? [];
            foreach ($photos as $index => $path) {
                if (basename($path) === $photo) {
                    unset($photos[$index]);
                    $checkin->meal_photos = array_values($photos);
                    $checkin->save();

                    return $path;
                }
            }

            return null;
        });

        if ($removed) {
            Storage::disk('public')->delete($removed);
        }

        return back();
    }

    public function commentMeal(Request $request, User $athlete, int $day): RedirectResponse
    {
        abort_unless(UserRole::from($request->user()->userType->name) === UserRole::Nutricionista, 403);
        abort_unless($athlete->userType->name === UserRole::Atleta->value, 404);
        abort_unless($day >= 1 && $day <= ChallengeSheet::TOTAL_DAYS, 404);

        $data = $request->validate(['comment' => 'required|string|max:500']);
        $checkin = Checkin::query()->firstOrNew(['user_id' => $athlete->id, 'day' => $day]);
        $checkin->meal_comment = $data['comment'];
        $checkin->save();

        return back();
    }

    public function storeAchievement(Request $request): RedirectResponse
    {
        $this->abortUnlessAthlete($request);
        abort_unless($this->access->pageOpen('conquistas'), 403, 'A página de conquistas está bloqueada.');

        if ($pending = $this->pendingStart($request)) {
            return $pending;
        }

        $data = $request->validate([
            'achieved_on' => 'nullable|date|after_or_equal:2026-11-09|before_or_equal:2026-12-08',
            'type' => 'required|in:'.implode(',', array_keys(ChallengeSheet::ACHIEVEMENTS)),
            'detail' => 'required|string|max:255',
        ]);

        $request->user()->achievements()->create([
            ...$data,
            'achieved_on' => $data['achieved_on'] ?? now()->toDateString(),
        ]);

        return to_route('athlete.progress.tab', 'conquistas');
    }

    public function updateAchievement(Request $request, Achievement $achievement): RedirectResponse
    {
        $this->abortUnlessAthlete($request);
        abort_unless($this->access->pageOpen('conquistas'), 403, 'A página de conquistas está bloqueada.');

        if ($pending = $this->pendingStart($request)) {
            return $pending;
        }
        abort_unless($achievement->user_id === $request->user()->id, 403);

        $achievement->update($request->validate([
            'detail' => 'required|string|max:255',
        ]));

        return to_route('athlete.progress.tab', 'conquistas');
    }

    public function commentAchievement(Request $request, Achievement $achievement): RedirectResponse
    {
        abort_unless(UserRole::from($request->user()->userType->name) === UserRole::Nutricionista, 403);
        abort_unless($achievement->user->userType->name === UserRole::Atleta->value, 403);

        $achievement->update([
            ...$request->validate([
                'comment' => 'required|string|max:500',
                'emotion' => 'required|string|max:16',
            ]),
            'nutritionist' => $request->user()->name,
        ]);

        return back();
    }

    public function destroyAchievement(Request $request, Achievement $achievement): RedirectResponse
    {
        $this->abortUnlessAthlete($request);
        abort_unless($this->access->pageOpen('conquistas'), 403, 'A página de conquistas está bloqueada.');

        if ($pending = $this->pendingStart($request)) {
            return $pending;
        }
        abort_unless($achievement->user_id === $request->user()->id, 403);

        $achievement->delete();

        return to_route('athlete.progress.tab', 'conquistas');
    }

    private function page(User $athlete, bool $canEdit, bool $canComment, ?string $tab = null, bool $adminPreview = false): Response
    {
        return Inertia::render('dashboard/Evolucao', [
            ...ChallengeSheet::payload($athlete),
            'access' => $adminPreview ? array_replace($this->access->payload(), [
                'pages' => array_fill_keys(['dias', 'conquistas', 'resumo', 'shopping'], true),
                'editableDays' => [], 'entryMode' => 'closed',
            ]) : $this->access->payload(),
            'adminPreview' => $adminPreview,
            'tab' => $tab,
            'canEdit' => $canEdit,
            'canComment' => $canComment,
            'athleteId' => $canComment ? $athlete->id : null,
            'athleteName' => $adminPreview ? 'Visualização do atleta' : ($canComment ? $athlete->name : null),
            'nutritionistName' => User::query()
                ->where('user_type_id', UserType::idFor(UserRole::Nutricionista))
                ->orderBy('name')
                ->value('name'),
        ]);
    }

    public function shopping(Request $request): Response
    {
        if ($request->user()->isAthlete() && ! $this->access->pageOpen('shopping')) {
            return Inertia::render('dashboard/EvolutionLocked', [
                'feature' => 'Monte seu Cardápio',
                'access' => $this->access->payload(),
            ]);
        }

        $user = $request->user();
        $canSave = $user && UserRole::tryFrom($user->userType->name) === UserRole::Atleta;

        return Inertia::render('Compras', [
            'adminPreview' => $user->userType?->name === UserRole::Administrador->value,
            'weeks' => $this->shoppingWeeks(),
            'lists' => $canSave ? $this->shoppingLists($user) : [],
            'canSave' => $canSave,
            'catalog' => FoodGroup::catalog(),
        ]);
    }

    public function createShopping(Request $request): Response|RedirectResponse
    {
        $this->abortUnlessAthlete($request);

        if ($request->user()->isAthlete() && ! $this->access->pageOpen('shopping')) {
            return Inertia::render('dashboard/EvolutionLocked', [
                'feature' => 'Monte seu Cardápio',
                'access' => $this->access->payload(),
            ]);
        }


        $used = collect($this->shoppingLists($request->user()))->pluck('week');
        $free = $this->shoppingWeeks()->reject(fn (array $week) => $used->contains($week['week']))->values();

        if ($free->isEmpty()) {
            return to_route('alimentacao.compras');
        }

        return Inertia::render('ComprasForm', [
            'weeks' => $free,
            'week' => $free->first()['week'],
            'list' => null,
            'catalog' => FoodGroup::catalog(),
        ]);
    }

    public function editShopping(Request $request, int $week): Response
    {
        $this->abortUnlessAthlete($request);

        if ($request->user()->isAthlete() && ! $this->access->pageOpen('shopping')) {
            return Inertia::render('dashboard/EvolutionLocked', [
                'feature' => 'Monte seu Cardápio',
                'access' => $this->access->payload(),
            ]);
        }


        $list = collect($this->shoppingLists($request->user()))->firstWhere('week', $week);
        abort_if($list === null, 404);

        return Inertia::render('ComprasForm', [
            'weeks' => $this->shoppingWeeks()->where('week', $week)->values(),
            'week' => $week,
            'list' => $list,
            'catalog' => FoodGroup::catalog(),
        ]);
    }

    public function storeShopping(Request $request): RedirectResponse
    {
        return $this->saveShopping($request, true);
    }

    public function updateShopping(Request $request): RedirectResponse
    {
        return $this->saveShopping($request, false);
    }

    private function saveShopping(Request $request, bool $new): RedirectResponse
    {
        $this->abortUnlessAthlete($request);
        abort_unless($this->access->pageOpen('shopping'), 403, 'Monte seu Cardápio ainda não está disponível.');

        $data = $request->validate([
            'week' => 'required|integer|between:1,7',
            'items' => 'array|max:300',
            'items.*' => 'string|max:120',
            'extras' => 'array|max:30',
            'extras.*' => 'nullable|string|max:120',
        ]);

        $items = array_values(array_unique(array_filter($data['items'] ?? [])));
        $extras = array_values(array_filter(array_map(
            fn ($item) => is_string($item) ? trim($item) : '',
            $data['extras'] ?? [],
        )));

        $profile = AthleteProfile::query()->firstOrNew(['user_id' => $request->user()->id]);
        $lists = $profile->shopping_lists ?? [];
        $key = (string) $data['week'];

        if ($new && isset($lists[$key])) {
            throw ValidationException::withMessages(['week' => 'Essa semana já tem uma lista. Edite a existente.']);
        }

        if ($items === [] && $extras === []) {
            unset($lists[$key]);
        } else {
            $lists[$key] = ['items' => $items, 'extras' => $extras];
        }

        $profile->shopping_lists = $lists === [] ? null : $lists;
        $profile->save();

        return to_route('alimentacao.compras');
    }

    /** @return \Illuminate\Support\Collection<int, array{week: int, period: string}> */
    private function shoppingWeeks()
    {
        return collect(ChallengeSheet::WEEKS)->map(function (array $range, int $week) {
            [$from, $to] = $range;

            return [
                'week' => $week,
                'period' => Carbon::parse(ChallengeSheet::dateForDay($from))->format('d/m').' a '.Carbon::parse(ChallengeSheet::dateForDay($to))->format('d/m'),
            ];
        })->values();
    }

    /** @return list<array{week: int, period: string, items: list<string>, extras: list<string>}> */
    private function shoppingLists(User $user): array
    {
        $saved = $user->athleteProfile?->shopping_lists ?? [];

        return $this->shoppingWeeks()->map(function (array $week) use ($saved) {
            $row = $saved[(string) $week['week']] ?? null;

            if (! is_array($row) || (($row['items'] ?? []) === [] && ($row['extras'] ?? []) === [])) {
                return null;
            }

            return [
                'week' => $week['week'],
                'period' => $week['period'],
                'items' => array_values($row['items'] ?? []),
                'extras' => array_values($row['extras'] ?? []),
            ];
        })->filter()->values()->all();
    }

    /** Evolução só libera depois do Primeiro dia e das Metas. */
    private function pendingStart(Request $request): ?RedirectResponse
    {
        $profile = $request->user()->athleteProfile;

        return match (true) {
            ! $profile?->welcomed_at => to_route('athlete.start.first-day'),
            ! $profile->goals_set_at => to_route('athlete.start.goals'),
            default => null,
        };
    }

    private function abortUnlessAthlete(Request $request): void
    {
        abort_unless(UserRole::from($request->user()->userType->name) === UserRole::Atleta, 403);
    }
}

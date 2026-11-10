<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ChallengePayment;
use App\Models\Checkin;
use App\Models\Food;
use App\Models\FoodGroup;
use App\Models\ScoreRule;
use App\Models\User;
use App\Models\UserType;
use App\Services\Asaas\AsaasClient;
use App\Services\Asaas\AsaasException;
use App\Support\ChallengeAccess;
use App\Support\ChallengeSheet;
use App\Support\DayLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $role = $this->role($request);

        if ($role === UserRole::Administrador) {
            return Inertia::render('dashboard/Admin', [
                'paymentOverview' => fn () => $this->paymentOverview(),
                'dailyPayments' => fn () => $this->dailyPayments(),
                'counts' => UserType::query()->withCount('users')->orderBy('id')->get(),
            ]);
        }

        if ($role === UserRole::Atleta) {
            if (! $request->user()->welcome_acknowledged_at) {
                return to_route('athlete.welcome');
            }

            $user = $request->user();
            $user->load(['checkins', 'achievements', 'athleteProfile']);

            if (! $user->athleteProfile?->welcomed_at) {
                return to_route('athlete.start.first-day');
            }

            if (! $user->athleteProfile->goals_set_at) {
                return to_route('athlete.start.goals');
            }

            $progress = ChallengeSheet::payload($user);
            $scoreRules = ScoreRule::grouped();

            return Inertia::render('dashboard/Atleta', [
                'total' => $progress['total'],
                'days' => $progress['days'],
                'weeks' => $progress['weeks'],
                'dailyScores' => $scoreRules['daily'],
                'bonusScores' => $scoreRules['bonus'],
            ]);
        }

        return Inertia::render('dashboard/Equipe', [
            'athleteCount' => User::query()->where('user_type_id', UserType::idFor(UserRole::Atleta))->count(),
            'paymentOverview' => $role === UserRole::Nutricionista ? fn () => $this->paymentOverview() : null,
            'dailyPayments' => $role === UserRole::Nutricionista ? fn () => $this->dailyPayments() : null,
        ]);
    }

    private function dailyPayments(): array
    {
        $today = today('America/Sao_Paulo');
        $timezone = config('app.timezone');

        return ChallengePayment::query()
            ->whereIn('status', ChallengePayment::PAID_STATUSES)
            ->where('confirmed_at', '>=', $today->copy()->setTimezone($timezone))
            ->where('confirmed_at', '<', $today->copy()->addDay()->setTimezone($timezone))
            ->with('user:id,name')
            ->orderByDesc('confirmed_at')
            ->orderByDesc('id')
            ->get(['id', 'user_id', 'value', 'confirmed_at'])
            ->map(fn (ChallengePayment $payment) => [
                'id' => $payment->id,
                'athlete' => $payment->user?->name,
                'amount' => (float) $payment->value,
                'time' => $payment->confirmed_at->copy()->setTimezone('America/Sao_Paulo')->format('H:i'),
            ])
            ->all();
    }

    private function paymentOverview(): array
    {
        $statuses = [
            ChallengePayment::RECEIVED,
            ChallengePayment::CONFIRMED,
            ChallengePayment::PENDING,
            ChallengePayment::OVERDUE,
        ];

        $charges = ChallengePayment::query()
            ->whereIn('status', [...$statuses, 'RECEIVED_IN_CASH'])
            ->select('user_id', 'value')
            ->selectRaw(
                'CASE WHEN status = ? AND due_date < ? THEN ? WHEN status = ? THEN ? ELSE status END AS category',
                [ChallengePayment::PENDING, today('America/Sao_Paulo')->toDateString(), ChallengePayment::OVERDUE, 'RECEIVED_IN_CASH', ChallengePayment::RECEIVED],
            );

        $totals = DB::query()->fromSub($charges, 'charges')
            ->selectRaw('category, COUNT(*) AS quantity, COUNT(DISTINCT user_id) AS customers, SUM(value) AS amount')
            ->groupBy('category')
            ->get()
            ->keyBy('category');

        return array_map(function (string $status) use ($totals) {
            $total = $totals->get($status);

            return [
                'status' => $status,
                'quantity' => (int) ($total->quantity ?? 0),
                'customers' => (int) ($total->customers ?? 0),
                'amount' => (float) ($total->amount ?? 0),
            ];
        }, $statuses);
    }

    public function welcomePreview(Request $request): Response
    {
        $this->abortUnless($request, UserRole::Administrador);

        return Inertia::render('dashboard/BoasVindas', ['adminPreview' => true]);
    }

    public function releaseWelcomePreview(Request $request): Response
    {
        $this->abortUnless($request, UserRole::Administrador);

        return Inertia::render('dashboard/ReleaseWelcomePreview');
    }

    public function payments(Request $request, AsaasClient $asaas): Response
    {
        $role = $this->role($request);
        abort_unless(in_array($role, [UserRole::Administrador, UserRole::Nutricionista], true), 403);

        $canViewBalance = $role === UserRole::Administrador;
        $today = today('America/Sao_Paulo')->toDateString();

        $filters = $request->validate([
            'status' => ['nullable', 'string', 'max:64'],
            'billing_type' => ['nullable', 'string', 'max:64'],
            'due_from' => ['nullable', 'date_format:Y-m-d'],
            'due_to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $payments = ChallengePayment::query();

        if ($status = $filters['status'] ?? null) {
            $payments->where('status', $status);
        }

        if (($filters['billing_type'] ?? null) === 'UNDEFINED') {
            $payments->where(fn ($query) => $query->where('billing_type', 'UNDEFINED')->orWhereNull('billing_type'));
        } elseif ($billingType = $filters['billing_type'] ?? null) {
            $payments->where('billing_type', $billingType);
        }

        if ($from = $filters['due_from'] ?? null) {
            $payments->whereDate('due_date', '>=', $from);
        }

        if ($to = $filters['due_to'] ?? null) {
            $payments->whereDate('due_date', '<=', $to);
        }

        return Inertia::render('dashboard/Payments', [
            'filters' => $filters,
            'statusSummary' => fn () => $this->paymentSummary('status'),
            'billingTypeSummary' => fn () => $this->paymentSummary('billing_type'),
            'canViewBalance' => $canViewBalance,
            'accountBalance' => $canViewBalance ? Inertia::defer(function () use ($asaas) {
                try {
                    return $asaas->getBalance();
                } catch (AsaasException) {
                    return null;
                }
            }) : null,
            'payments' => $payments->with('user:id,name,email')
                ->latest('id')
                ->paginate(25, [
                    'id',
                    'user_id',
                    'asaas_payment_id',
                    'status',
                    'billing_type',
                    'value',
                    'due_date',
                    'confirmed_at',
                    'created_at',
                ])
                ->appends($filters)
                ->through(fn (ChallengePayment $payment) => [
                    'id' => $payment->id,
                    'athlete' => $payment->user?->name,
                    'email' => $payment->user?->email,
                    'asaasPaymentId' => $payment->asaas_payment_id,
                    'status' => $payment->status,
                    'isPastDue' => $payment->due_date !== null && $payment->due_date->toDateString() < $today,
                    'billingType' => $payment->billing_type,
                    'value' => $payment->value,
                    'dueDate' => $payment->due_date?->format('d/m/Y'),
                    'confirmedAt' => $payment->confirmed_at?->format('d/m/Y H:i'),
                    'createdAt' => $payment->created_at?->format('d/m/Y H:i'),
                ]),
        ]);
    }

    private function paymentSummary(string $column): array
    {
        $group = $column === 'billing_type' ? "COALESCE(billing_type, 'UNDEFINED')" : 'status';

        $summary = ChallengePayment::query()
            ->selectRaw("$group AS category, COUNT(*) AS quantity, SUM(value) AS amount")
            ->groupByRaw($group)
            ->orderBy('category')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category,
                'quantity' => (int) $row->quantity,
                'amount' => (float) $row->amount,
            ]);

        $alwaysVisible = $column === 'status' ? ChallengePayment::PENDING : 'PIX';

        if (! $summary->contains('category', $alwaysVisible)) {
            $summary->prepend(['category' => $alwaysVisible, 'quantity' => 0, 'amount' => 0]);
        }

        return $summary->all();
    }

    public function users(Request $request): Response
    {
        $this->abortUnless($request, UserRole::Administrador);

        return Inertia::render('dashboard/Users', [
            'users' => User::query()->with('userType:id,name')->orderBy('name')->get(['id', 'name', 'email', 'user_type_id', 'challenge_paid_at', 'challenge_courtesy_at']),
        ]);
    }

    public function createUser(Request $request): Response
    {
        $this->abortUnless($request, UserRole::Administrador);

        return Inertia::render('dashboard/UserCreate', [
            'types' => UserType::query()->orderBy('id')->get(['id', 'name']),
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $this->abortUnless($request, UserRole::Administrador);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Password::defaults()],
            'user_type_id' => 'required|integer|exists:user_types,id',
            'courtesy' => 'sometimes|boolean',
        ]);

        $courtesy = $request->boolean('courtesy');
        if ($courtesy && (int) $data['user_type_id'] !== UserType::idFor(UserRole::Atleta)) {
            throw ValidationException::withMessages(['courtesy' => 'A cortesia é exclusiva para atletas.']);
        }

        DB::transaction(function () use ($data, $courtesy, $request) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'user_type_id' => $data['user_type_id'],
            ]);
            $user->forceFill([
                'email_verified_at' => now(),
                'challenge_courtesy_at' => $courtesy ? now() : null,
                'challenge_courtesy_by' => $courtesy ? $request->user()->id : null,
            ])->save();
        });

        return to_route('admin.users');
    }

    public function updateCourtesy(Request $request, User $athlete): RedirectResponse
    {
        $this->abortUnless($request, UserRole::Administrador);
        abort_unless($athlete->isAthlete(), 403);
        $data = $request->validate(['courtesy' => 'required|boolean']);
        $grant = (bool) $data['courtesy'];
        $athlete->forceFill([
            'challenge_courtesy_at' => $grant ? ($athlete->challenge_courtesy_at ?? now()) : null,
            'challenge_courtesy_by' => $grant ? ($athlete->challenge_courtesy_by ?? $request->user()->id) : null,
        ])->save();

        return to_route('admin.users');
    }

    public function foods(Request $request): Response
    {
        $this->abortUnless($request, UserRole::Administrador);

        return Inertia::render('dashboard/Foods', [
            'groups' => FoodGroup::query()->with('foods')->orderBy('position')->orderBy('id')->get(),
        ]);
    }

    public function storeFood(Request $request): RedirectResponse
    {
        $this->abortUnless($request, UserRole::Administrador);

        $data = $request->validate([
            'category' => 'required|string|max:80',
            'group' => 'nullable|string|max:80',
            'name' => 'required|string|max:120',
        ]);

        $group = FoodGroup::query()->firstOrCreate(
            ['category' => trim($data['category']), 'name' => trim($data['group'] ?? '')],
            ['position' => ((int) FoodGroup::query()->max('position')) + 1],
        );

        $group->foods()->create([
            'name' => trim($data['name']),
            'position' => ((int) $group->foods()->max('position')) + 1,
        ]);

        return to_route('admin.foods');
    }

    public function toggleFood(Request $request, Food $food): RedirectResponse
    {
        $this->abortUnless($request, UserRole::Administrador);

        $food->update(['active' => ! $food->active]);

        return to_route('admin.foods');
    }

    public function destroyFood(Request $request, Food $food): RedirectResponse
    {
        $this->abortUnless($request, UserRole::Administrador);

        $group = $food->group;
        $food->delete();

        if ($group && $group->foods()->doesntExist() && ($group->notes ?? []) === []) {
            $group->delete();
        }

        return to_route('admin.foods');
    }

    public function scoring(Request $request): Response
    {
        return Inertia::render('Pontuacao', [
            ...ScoreRule::grouped(),
            'canEdit' => $this->role($request) === UserRole::Administrador,
        ]);
    }

    public function updateScoring(Request $request): RedirectResponse
    {
        $this->abortUnless($request, UserRole::Administrador);

        $data = $request->validate([
            'daily' => 'required|array',
            'daily.*.id' => 'required|exists:score_rules,id',
            'daily.*.label' => 'required|string|max:160',
            'daily.*.points' => 'required|integer|min:0|max:50',
            'bonus' => 'required|array',
            'bonus.*.id' => 'required|exists:score_rules,id',
            'bonus.*.label' => 'required|string|max:160',
            'bonus.*.points' => 'required|integer|min:0|max:50',
        ]);

        foreach ([...$data['daily'], ...$data['bonus']] as $row) {
            ScoreRule::query()->whereKey($row['id'])->update([
                'label' => $row['label'],
                'points' => $row['points'],
            ]);
        }

        return to_route('pontuacao');
    }

    public function athletes(Request $request): Response
    {
        $role = $this->role($request);

        if ($role === UserRole::Administrador || $role === UserRole::Atleta) {
            abort(403);
        }

        return Inertia::render('dashboard/Athletes', [
            'athletes' => User::query()
                ->where('user_type_id', UserType::idFor(UserRole::Atleta))
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function athleteCheckins(Request $request): Response
    {
        $role = $this->role($request);
        abort_unless(in_array($role, [UserRole::Administrador, UserRole::Nutricionista], true), 403);

        $completed = fn ($query) => $query->where('checked_in', true);
        $athletes = User::query()
            ->select(['id', 'name'])
            ->where('user_type_id', UserType::idFor(UserRole::Atleta))
            ->whereHas('checkins', $completed)
            ->withCount(['checkins as completed_checkins' => $completed])
            ->withMax(['checkins as last_checkin_day' => $completed], 'day')
            ->orderByDesc('last_checkin_day')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(25)
            ->through(fn (User $athlete) => [
                'id' => $athlete->id,
                'name' => $athlete->name,
                'checkins' => (int) $athlete->completed_checkins,
                'lastDay' => (int) $athlete->last_checkin_day,
                'lastDate' => \Illuminate\Support\Carbon::parse(ChallengeSheet::dateForDay((int) $athlete->last_checkin_day))->format('d/m/Y'),
            ]);

        return Inertia::render('dashboard/AthleteCheckins', [
            'athletes' => $athletes,
            'canViewProgress' => $role === UserRole::Nutricionista,
        ]);
    }

    public function athleteAchievements(Request $request): Response
    {
        $role = $this->role($request);
        abort_unless(in_array($role, [UserRole::Administrador, UserRole::Nutricionista], true), 403);

        $categories = [
            'cantinho' => ChallengeSheet::ACHIEVEMENTS['cantinho'][0],
            'resistido' => ChallengeSheet::ACHIEVEMENTS['resistido'][0],
            'performance' => ChallengeSheet::ACHIEVEMENTS['performance'][0],
        ];
        $category = $request->query('categoria', 'cantinho');
        abort_unless(is_string($category) && isset($categories[$category]), 404);

        $inCategory = fn ($query) => $query->where('type', $category);
        $athletes = User::query()
            ->select(['id', 'name'])
            ->where('user_type_id', UserType::idFor(UserRole::Atleta))
            ->whereHas('achievements', $inCategory)
            ->withCount(['achievements' => $inCategory])
            ->with(['achievements' => fn ($query) => $inCategory($query)
                ->select(['id', 'user_id', 'achieved_on', 'detail'])
                ->orderByDesc('achieved_on')
                ->orderByDesc('id')])
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(25)
            ->appends(['categoria' => $category])
            ->through(fn (User $athlete) => [
                'id' => $athlete->id,
                'name' => $athlete->name,
                'achievements_count' => (int) $athlete->achievements_count,
                'records' => $athlete->achievements->map(fn ($record) => [
                    'id' => $record->id,
                    'detail' => $record->detail,
                    'date' => $record->achieved_on->format('d/m/Y'),
                    'day' => ChallengeSheet::dayOn(Carbon::parse($record->achieved_on->toDateString(), ChallengeAccess::TIMEZONE)),
                ]),
            ]);

        return Inertia::render('dashboard/AthleteAchievements', [
            'athletes' => $athletes,
            'categories' => $categories,
            'category' => $category,
            'canViewProgress' => $role === UserRole::Nutricionista,
        ]);
    }

    public function athleteMeals(Request $request): Response
    {
        abort_unless(in_array($this->role($request), [UserRole::Administrador, UserRole::Nutricionista], true), 403);

        $hasMeal = function ($query) {
            $query->where(function ($query) {
                $query->where('strength', true)
                    ->orWhere('plus_count', '>', 0)
                    ->orWhere('notes->strength', '<>', '')
                    ->orWhere('notes->plus', '<>', '')
                    ->orWhere('meal_photo', '<>', '')
                    ->orWhereJsonLength('meal_photos', '>', 0);

                foreach (DayLog::FOOD_KEYS as $key) {
                    $query->orWhere('details->food->'.$key, true);
                }
            });
        };

        $athletes = User::query()
            ->select(['id', 'name'])
            ->where('user_type_id', UserType::idFor(UserRole::Atleta))
            ->whereHas('checkins', $hasMeal)
            ->with(['checkins' => function ($query) use ($hasMeal) {
                $hasMeal($query);
                $query->select(['id', 'user_id', 'day', 'strength', 'plus_count', 'notes', 'details', 'meal_photo', 'meal_photos'])
                    ->orderByDesc('day');
            }])
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->through(fn (User $athlete) => [
                'id' => $athlete->id,
                'name' => $athlete->name,
                'days' => $athlete->checkins->map(fn ($day) => [
                    'id' => $day->id,
                    'day' => $day->day,
                    'date' => \Illuminate\Support\Carbon::parse(ChallengeSheet::dateForDay($day->day))->format('d/m/Y'),
                    'achieved' => $day->strength,
                    'food' => DayLog::normalize($day->details)['food'],
                    'foodNote' => $day->notes['strength'] ?? '',
                    'luxuryCount' => $day->plus_count,
                    'luxuryNote' => $day->notes['plus'] ?? '',
                    'photos' => array_map(fn (string $path) => route('equipe.meals.photo', [
                        'checkin' => $day->id,
                        'photo' => basename($path),
                    ], absolute: false), $day->mealPhotoPaths()),
                ]),
            ]);

        return Inertia::render('dashboard/AthleteMeals', ['athletes' => $athletes]);
    }

    public function athleteMealPhoto(Request $request, Checkin $checkin, string $photo): StreamedResponse
    {
        abort_unless(in_array($this->role($request), [UserRole::Administrador, UserRole::Nutricionista], true), 403);
        abort_unless($checkin->user?->isAthlete(), 404);

        $path = collect($checkin->mealPhotoPaths())->first(fn (string $path) => basename($path) === $photo);
        $disk = Storage::disk('public');
        abort_unless($path && $disk->exists($path), 404);

        return $disk->response($path, $photo, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    private function role(Request $request): UserRole
    {
        return UserRole::from($request->user()->userType->name);
    }

    private function abortUnless(Request $request, UserRole $role): void
    {
        abort_unless($this->role($request) === $role, 403);
    }
}

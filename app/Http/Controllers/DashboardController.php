<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ChallengePayment;
use App\Models\Food;
use App\Models\FoodGroup;
use App\Models\ScoreRule;
use App\Models\User;
use App\Models\UserType;
use App\Support\ChallengeSheet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $role = $this->role($request);

        if ($role === UserRole::Administrador) {
            return Inertia::render('dashboard/Admin', [
                'counts' => UserType::query()->withCount('users')->orderBy('id')->get(),
            ]);
        }

        if ($role === UserRole::Atleta) {
            $user = $request->user();
            $user->load(['checkins', 'achievements', 'athleteProfile']);

            if (! $user->athleteProfile?->welcomed_at) {
                return to_route('athlete.start.first-day');
            }

            if (! $user->athleteProfile->goals_set_at) {
                return to_route('athlete.start.goals');
            }

            $profile = $user->athleteProfile;

            return Inertia::render('dashboard/Atleta', [
                'total' => collect(ChallengeSheet::weeks($user))->sum('total'),
                'baseline' => [
                    'weight' => $profile->weight,
                    'waist' => $profile->waist,
                    'abdomen' => $profile->abdomen,
                    'hip' => $profile->hip,
                    'photo' => $profile->photo ? '/storage/'.$profile->photo : null,
                ],
            ]);
        }

        return Inertia::render('dashboard/Equipe', [
            'athleteCount' => User::query()->where('user_type_id', UserType::idFor(UserRole::Atleta))->count(),
            'canViewPayments' => $role === UserRole::Nutricionista,
        ]);
    }

    public function payments(Request $request): Response
    {
        $role = $this->role($request);
        abort_unless(in_array($role, [UserRole::Administrador, UserRole::Nutricionista], true), 403);

        return Inertia::render('dashboard/Payments', [
            'payments' => ChallengePayment::query()
                ->with('user:id,name,email')
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
                ->through(fn (ChallengePayment $payment) => [
                    'id' => $payment->id,
                    'athlete' => $payment->user?->name,
                    'email' => $payment->user?->email,
                    'asaasPaymentId' => $payment->asaas_payment_id,
                    'status' => $payment->status,
                    'billingType' => $payment->billing_type,
                    'value' => $payment->value,
                    'dueDate' => $payment->due_date?->format('d/m/Y'),
                    'confirmedAt' => $payment->confirmed_at?->format('d/m/Y H:i'),
                    'createdAt' => $payment->created_at?->format('d/m/Y H:i'),
                ]),
        ]);
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

    private function role(Request $request): UserRole
    {
        return UserRole::from($request->user()->userType->name);
    }

    private function abortUnless(Request $request, UserRole $role): void
    {
        abort_unless($this->role($request) === $role, 403);
    }
}

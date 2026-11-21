<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\Checkin;
use App\Models\User;
use App\Support\ChallengeContext;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ChallengeController extends Controller
{
    private function admin(Request $request): void
    {
        abort_unless($request->user()->userType?->name === 'administrador', 403);
    }

    public function index(Request $request)
    {
        $this->admin($request);

        return Inertia::render('dashboard/Challenges', [
            'challenges' => Challenge::orderByDesc('id')->get()->map(fn ($challenge) => [
                ...$challenge->toArray(), 'default_start_date' => $challenge->default_start_date->toDateString(),
                'end_date' => $challenge->default_end_date,
                'started' => Checkin::withoutGlobalScopes()->where('challenge_id', $challenge->id)->exists(),
                'participants' => DB::table('challenge_participations')->where('challenge_id', $challenge->id)->count(),
            ]),
            'objectives' => Challenge::OBJECTIVES,
            'viewChallengeId' => ChallengeContext::challenge()->id,
        ]);
    }

    private function data(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'default_start_date' => ['required', 'date_format:Y-m-d'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:255'],
            'objectives' => ['required', 'array', 'min:1'],
            'objectives.*' => ['required', 'distinct', Rule::in(array_keys(Challenge::OBJECTIVES))],
            'accepting_participants' => ['required', 'boolean'],
        ]);
    }

    public function store(Request $request)
    {
        $this->admin($request);
        Challenge::create($this->data($request));

        return back()->with('status', 'Desafio criado.');
    }

    public function update(Request $request, Challenge $challenge)
    {
        $this->admin($request);
        $data = $this->data($request);
        DB::transaction(function () use ($data, $challenge) {
            $challenge = Challenge::lockForUpdate()->findOrFail($challenge->id);
            $started = Checkin::withoutGlobalScopes()->where('challenge_id', $challenge->id)->exists();
            if ($started && ($challenge->default_start_date->toDateString() !== $data['default_start_date']
                || $challenge->duration_days !== (int) $data['duration_days'] || $challenge->objectives !== $data['objectives'])) {
                throw ValidationException::withMessages(['challenge' => 'Este desafio já tem registros. Datas, duração e objetivos devem ser preservados. Crie outro desafio para uma nova configuração.']);
            }
            $oldDate = $challenge->default_start_date->toDateString();
            $challenge->update($data);
            if (! $started && $oldDate !== $data['default_start_date']) {
                DB::table('challenge_participations')->where('challenge_id', $challenge->id)->where('start_date', $oldDate)->whereNull('start_date_confirmed_at')
                    ->update(['start_date' => $data['default_start_date'], 'updated_at' => now()]);
            }
        });

        return back();
    }

    public function makeDefault(Request $request, Challenge $challenge)
    {
        $this->admin($request);
        DB::transaction(function () use ($challenge) {
            Challenge::query()->lockForUpdate()->get();
            Challenge::query()->update(['is_default' => false]);
            $challenge->update(['is_default' => true]);
        });

        return back();
    }

    public function selectView(Request $request, Challenge $challenge)
    {
        abort_unless(in_array($request->user()->userType?->name, ['administrador', 'nutricionista'], true), 403);
        $request->session()->put('view_challenge_id', $challenge->id);

        return back();
    }

    public function show(Request $request)
    {
        abort_unless($request->user()->isAthlete(), 403);
        $user = $request->user();
        $challenge = ChallengeContext::challenge($user);
        DB::table('challenge_participations')->insertOrIgnore([
            'user_id' => $user->id, 'challenge_id' => $challenge->id,
            'start_date' => $challenge->default_start_date->toDateString(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $history = DB::table('challenge_participations')->join('challenges', 'challenges.id', '=', 'challenge_participations.challenge_id')
            ->where('user_id', $user->id)->get(['challenge_id', 'start_date', 'challenges.name', 'duration_days'])
            ->map(function ($row) use ($user) {
                $snapshot = clone $user;
                $snapshot->active_challenge_id = $row->challenge_id;
                $snapshot->unsetRelations();

                return [...(array) $row,
                    'end_date' => Carbon::parse($row->start_date)->addDays($row->duration_days - 1)->toDateString(),
                    'profile' => $snapshot->athleteProfile?->only(['weight', 'waist', 'abdomen', 'hip', 'movement_level', 'movement_minutes', 'water_liters', 'sleep_goal', 'skill', 'strength_goal']),
                    'checkins' => $snapshot->checkins()->where('checked_in', true)->count(),
                ];
            });

        return Inertia::render('dashboard/MyChallenge', [
            'challenge' => ChallengeContext::summary($user),
            'canChangeStart' => ! $user->checkins()->exists() && ! ChallengeContext::startConfirmed($user),
            'startConfirmed' => ChallengeContext::startConfirmed($user),
            'needsStartChoice' => ChallengeContext::needsStartChoice($user),
            'available' => Challenge::where('accepting_participants', true)->where('id', '!=', $challenge->id)->get()
                ->map(fn ($item) => [...$item->toArray(), 'default_start_date' => $item->default_start_date->toDateString(), 'end_date' => $item->default_end_date]),
            'history' => $history,
            'objectiveLabels' => Challenge::OBJECTIVES,
        ]);
    }

    public function updateStart(Request $request)
    {
        abort_unless($request->user()->isAthlete(), 403);
        $data = $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.Carbon::today('America/Sao_Paulo')->toDateString()],
            'confirmed' => ['required', 'accepted'],
        ]);
        DB::transaction(function () use ($request, $data) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);
            $challenge = ChallengeContext::challenge($user);
            Challenge::lockForUpdate()->findOrFail($challenge->id);
            if ($user->checkins()->exists()) {
                throw ValidationException::withMessages(['start_date' => 'O início não pode ser alterado após o primeiro registro diário.']);
            }
            if (ChallengeContext::startConfirmed($user)) {
                throw ValidationException::withMessages(['start_date' => 'A data de início já foi confirmada. Essa escolha só pode ser feita uma vez por desafio.']);
            }
            if (Carbon::parse($data['start_date'])->lt($challenge->default_start_date)) {
                throw ValidationException::withMessages(['start_date' => 'Escolha a data padrão do desafio ou uma data posterior.']);
            }
            DB::table('challenge_participations')->updateOrInsert(['user_id' => $user->id, 'challenge_id' => $challenge->id], [
                'start_date' => $data['start_date'], 'start_date_confirmed_at' => now(), 'updated_at' => now(), 'created_at' => now(),
            ]);
        });

        return to_route('athlete.progress.tab', 'dias');
    }

    public function join(Request $request, Challenge $challenge)
    {
        abort_unless($request->user()->isAthlete() && $challenge->accepting_participants, 403);
        DB::transaction(function () use ($request, $challenge) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);
            $current = ChallengeContext::summary($user);
            if ($current['id'] !== $challenge->id && $user->checkins()->exists()
                && Carbon::today('America/Sao_Paulo')->lte(Carbon::parse($current['end_date']))) {
                throw ValidationException::withMessages(['challenge' => 'Conclua o período do desafio atual antes de participar de outro.']);
            }
            DB::table('challenge_participations')->insertOrIgnore([
                'user_id' => $user->id, 'challenge_id' => $challenge->id,
                'start_date' => $challenge->default_start_date->toDateString(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            $user->forceFill(['active_challenge_id' => $challenge->id])->save();
        });

        return to_route('athlete.challenge');
    }
}

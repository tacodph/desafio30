<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\Checkin;
use App\Models\ScoreRule;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserType;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class ChallengeSheet
{
    public const START = '2026-11-09';

    public const TOTAL_DAYS = 30;

    /** @var array<int, array{0: int, 1: int}> */
    public const WEEKS = [
        1 => [1, 7],
        2 => [8, 14],
        3 => [15, 21],
        4 => [22, 28],
        5 => [29, 30],
    ];

    /** @var array<string, array{0: string, 1: int}> */
    public const ACHIEVEMENTS = [
        'skill_demo' => ['Demonstração da habilidade no dia 30', 15],
        'motivate' => ['Motivar um amigo desanimado', 5],
        'first_time' => ['Primeira vez acertando algo novo', 5],
        'cantinho' => ['Cantinho da vitória', 0],
        'resistido' => ['Resistido do dia', 0],
        'performance' => ['Performance', 0],
    ];

    public static function dateForDay(int $day): string
    {
        return Carbon::parse(self::START)->addDays($day - 1)->toDateString();
    }

    public static function dayOn(CarbonInterface $date): int
    {
        $start = Carbon::parse(self::START, ChallengeAccess::TIMEZONE)->startOfDay()->getTimestamp();
        $stamp = $date->copy()->setTimezone(ChallengeAccess::TIMEZONE)->startOfDay()->getTimestamp();

        return (int) (($stamp - $start) / 86400) + 1;
    }

    public static function weekOf(int $day): int
    {
        foreach (self::WEEKS as $week => [$from, $to]) {
            if ($day >= $from && $day <= $to) {
                return $week;
            }
        }

        return $day < 1 ? 1 : count(self::WEEKS);
    }

    public static function currentWeek(?CarbonInterface $today = null): int
    {
        $day = self::dayOn($today ?? Carbon::today(ChallengeAccess::TIMEZONE));

        return self::weekOf(max(1, min(self::TOTAL_DAYS, $day)));
    }

    public static function dayPoints(Checkin $checkin, ?array $scores = null): int
    {
        $p = fn (string $key, int $default) => (int) (($scores ?? self::scoreMap())[$key] ?? $default);

        return ($checkin->checked_in ? $p('checkin', 1) : 0)
            + ($checkin->movement ? $p('movement', 3) : 0)
            + ($checkin->water ? $p('water', 1) : 0)
            + ($checkin->sleep ? $p('sleep', 1) : 0)
            + ($checkin->strength ? $p('strength', 2) : 0)
            + ($checkin->skill ? $p('skill', 2) : 0);
    }

    /** @return array<string, int> */
    private static function scoreMap(): array
    {
        return ScoreRule::map()->all();
    }

    /**
     * Detalhes do dia. Dias salvos antes dos detalhes existirem (só com os checks)
     * entram com os objetivos marcados como "manuais", para não perder pontos.
     *
     * @return array<string, mixed>
     */
    public static function detailsFor(?Checkin $checkin): array
    {
        if ($checkin?->details !== null) {
            return DayLog::normalize($checkin->details);
        }

        return DayLog::normalize([
            'manual' => collect(DayLog::MANUAL_KEYS)
                ->mapWithKeys(fn (string $key) => [$key => (bool) $checkin?->{$key}])
                ->all(),
        ]);
    }

    /** Dia com algo lançado (linhas vazias criadas por salvamentos antigos não contam). */
    private static function hasEntry(?Checkin $checkin): bool
    {
        if ($checkin === null) {
            return false;
        }

        $details = self::detailsFor($checkin);

        return $checkin->checked_in
            || $checkin->plus_count > 0
            || filled($checkin->notes)
            || $details['workouts'] !== []
            || $details['water_ml'] > 0
            || $details['sleep_hours'] > 0
            || in_array(true, $details['food'], true)
            || in_array(true, $details['manual'], true)
            || $details['skill'] !== '';
    }

    public static function payload(User $user): array
    {
        $user->loadMissing(['athleteProfile', 'achievements']);
        $user->load('checkins');
        $checkins = $user->checkins->keyBy('day');
        $weekdays = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb'];
        $scores = self::scoreMap();
        $days = [];

        for ($day = 1; $day <= self::TOTAL_DAYS; $day++) {
            $date = Carbon::parse(self::dateForDay($day));
            $checkin = $checkins->get($day);
            $days[] = [
                'day' => $day,
                'date' => $date->format('d/m'),
                'weekday' => $weekdays[$date->dayOfWeek],
                'week' => self::weekOf($day),
                'checked_in' => (bool) $checkin?->checked_in,
                'movement' => (bool) $checkin?->movement,
                'water' => (bool) $checkin?->water,
                'sleep' => (bool) $checkin?->sleep,
                'strength' => (bool) $checkin?->strength,
                'skill' => (bool) $checkin?->skill,
                'plus_count' => (int) ($checkin?->plus_count ?? 0),
                'details' => self::detailsFor($checkin),
                'saved' => self::hasEntry($checkin),
                'workout_photo' => $checkin?->workout_photo ? route('evolution.workout.photo', ['checkin' => $checkin->id, 'v' => basename($checkin->workout_photo)], absolute: false) : null,
                'workout_photo_id' => $checkin?->workout_photo ? basename($checkin->workout_photo) : null,
                'reactions' => (object) ($checkin?->reactions ?? []),
                'meal_photos' => array_map(fn (string $path) => [
                    'id' => basename($path),
                    'url' => route('equipe.meals.photo', ['checkin' => $checkin->id, 'photo' => basename($path)], absolute: false),
                ], $checkin?->mealPhotoPaths() ?? []),
                'meal_comment' => $checkin?->meal_comment ?? '',
                'meal_reaction' => $checkin?->meal_reaction,
                'notes' => array_merge([
                    'movement' => '',
                    'water' => '',
                    'sleep' => '',
                    'strength' => '',
                    'skill' => '',
                    'plus' => '',
                    'day' => '',
                ], is_array($checkin?->notes) ? $checkin->notes : []),
                'points' => $checkin ? self::dayPoints($checkin, $scores) : 0,
            ];
        }

        $profile = $user->athleteProfile;
        $elapsed = max(0, min(self::TOTAL_DAYS, self::dayOn(Carbon::today(ChallengeAccess::TIMEZONE)) - 1));
        $missed = 0;

        if ($profile?->penalty) {
            for ($day = 1; $day <= $elapsed; $day++) {
                if (! $checkins->get($day)?->checked_in) {
                    $missed++;
                }
            }
        }

        $weeks = self::weeksWithGroup($user);

        return [
            'currentWeek' => self::currentWeek(),
            'today' => max(1, min(self::TOTAL_DAYS, self::dayOn(Carbon::today(ChallengeAccess::TIMEZONE)))),
            'goals' => DayLog::goals($profile),
            'workoutTypes' => \App\Models\WorkoutType::query()->orderBy('id')->pluck('name')->all(),
            'weeklySkills' => array_values(array_map(
                fn ($picks) => array_slice(array_values(array_filter((array) $picks)), 0, 1),
                $profile?->weekly_skills ?? [],
            )),
            'skillCatalog' => Skill::catalog(),
            'profile' => [
                'movement_level' => $profile?->movement_level ?? '',
                'movement_minutes' => $profile?->movement_minutes ?? '',
                'water_liters' => $profile?->water_liters ?? '',
                'sleep_goal' => $profile?->sleep_goal ?? '',
                'skill' => $profile?->skill ?? '',
                'strength_goal' => $profile?->strength_goal ?? '',
                'test_1' => $profile?->test_1 ?? '',
                'test_15' => $profile?->test_15 ?? '',
                'test_30' => $profile?->test_30 ?? '',
                'test_45' => $profile?->test_45 ?? '',
                'penalty' => (bool) $profile?->penalty,
                'penalty_donated_on' => $profile?->penalty_donated_on?->toDateString() ?? '',
                'penalty_note' => $profile?->penalty_note ?? '',
            ],
            'days' => $days,
            'weeks' => $weeks,
            'achievements' => $user->achievements
                ->sortByDesc('achieved_on')
                ->values()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'achieved_on' => $item->achieved_on->toDateString(),
                    'type' => $item->type,
                    'label' => self::ACHIEVEMENTS[$item->type][0] ?? $item->type,
                    'points' => $scores[$item->type] ?? self::ACHIEVEMENTS[$item->type][1] ?? 0,
                    'detail' => $item->detail ?? '',
                    'emotion' => $item->emotion ?? '',
                    'comment' => $item->comment ?? '',
                    'nutritionist' => $item->nutritionist ?? '',
                ]),
            'types' => collect(self::ACHIEVEMENTS)->map(fn ($item, $type) => [
                'type' => $type,
                'label' => $item[0],
                'points' => $scores[$type] ?? $item[1],
            ])->values(),
            'scoreRules' => $scores,
            'total' => collect($weeks)->sum('total'),
            'penalty' => [
                'missed' => $missed,
                'amount' => $missed * 10,
            ],
        ];
    }

    /** @return list<array<string, int|string>> */
    public static function weeks(User $user): array
    {
        $user->loadMissing(['checkins', 'achievements']);
        $checkins = $user->checkins->keyBy('day');
        $scores = self::scoreMap();
        $rows = [];

        foreach (self::WEEKS as $week => [$from, $to]) {
            $slice = collect(range($from, $to))->map(fn (int $day) => $checkins->get($day));
            $daily = $slice->sum(fn ($checkin) => $checkin ? self::dayPoints($checkin, $scores) : 0);
            $checkinCount = $slice->filter(fn ($checkin) => $checkin?->checked_in)->count();
            $strength = $slice->filter(fn ($checkin) => $checkin?->strength)->count();
            $skill = $slice->filter(fn ($checkin) => $checkin?->skill)->count();
            $plus = (int) $slice->sum(fn ($checkin) => $checkin?->plus_count ?? 0);
            $fromDate = self::dateForDay($from);
            $toDate = self::dateForDay($to);
            $achievementPoints = $user->achievements
                ->filter(fn ($item) => $item->achieved_on->toDateString() >= $fromDate && $item->achieved_on->toDateString() <= $toDate)
                ->sum(fn ($item) => $scores[$item->type] ?? self::ACHIEVEMENTS[$item->type][1] ?? 0);
            $consistency = $checkinCount >= min(6, $to - $from + 1) ? (int) ($scores['consistency'] ?? 10) : 0;
            $goalsBonus = $skill >= min(3, $to - $from + 1) && $strength >= min(6, $to - $from + 1) ? 10 : 0;
            $plusBonus = min(2, $plus) * 5;

            $rows[] = [
                'week' => $week,
                'period' => Carbon::parse($fromDate)->format('d/m').' a '.Carbon::parse($toDate)->format('d/m'),
                'checkins' => $checkinCount,
                'daily' => $daily,
                'strength' => $strength,
                'skill' => $skill,
                'plus' => $plus,
                'plus_bonus' => $plusBonus,
                'consistency' => $consistency,
                'goals_bonus' => $goalsBonus,
                'achievements' => $achievementPoints,
                'total' => $daily + $plusBonus + $consistency + $goalsBonus + $achievementPoints,
            ];
        }

        return $rows;
    }

    /** @return list<array<string, int|string>> */
    private static function weeksWithGroup(User $user): array
    {
        $weeks = self::weeks($user);
        $group = self::weekCompletions();

        foreach ($weeks as &$row) {
            $row['athletes_done'] = $group['done'][$row['week']] ?? 0;
            $row['athletes_total'] = $group['total'];
        }
        unset($row);

        return $weeks;
    }

    /** Quantos atletas bateram a consistência: 6 dias, limitada à duração da semana. */
    private static function weekCompletions(): array
    {
        $ids = User::query()->where('user_type_id', UserType::idFor(UserRole::Atleta))->pluck('id');
        $done = array_fill_keys(array_keys(self::WEEKS), 0);

        if ($ids->isEmpty()) {
            return ['total' => 0, 'done' => $done];
        }

        $byUser = Checkin::query()
            ->whereIn('user_id', $ids)
            ->where('checked_in', true)
            ->get(['user_id', 'day'])
            ->groupBy('user_id');

        foreach (self::WEEKS as $week => [$from, $to]) {
            $need = min(6, $to - $from + 1);
            $done[$week] = $byUser->filter(
                fn ($days) => $days->filter(fn ($row) => $row->day >= $from && $row->day <= $to)->count() >= $need
            )->count();
        }

        return ['total' => $ids->count(), 'done' => $done];
    }
}

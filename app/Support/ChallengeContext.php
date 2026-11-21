<?php

namespace App\Support;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ChallengeContext
{
    public static function challenge(?User $user = null): Challenge
    {
        $user ??= request()->user();
        $id = $user?->active_challenge_id;
        if (request()->user() && ! request()->user()->isAthlete() && request()->hasSession()) {
            $id = request()->session()->get('view_challenge_id', $id);
        }

        $cache = request()->attributes->get('challenge_models', []);
        $key = $id ?? 'default';
        if (! isset($cache[$key])) {
            $cache[$key] = ($id ? Challenge::find($id) : null)
                ?? Challenge::where('is_default', true)->firstOrFail();
            request()->attributes->set('challenge_models', $cache);
        }

        return $cache[$key];
    }

    public static function startDate(?User $user = null): string
    {
        $user ??= request()->user();
        $challenge = self::challenge($user);

        return ($user?->isAthlete() ? DB::table('challenge_participations')->where('user_id', $user->id)
            ->where('challenge_id', $challenge->id)->value('start_date') : null)
            ?? $challenge->default_start_date->toDateString();
    }

    public static function objectives(?User $user = null): array
    {
        return self::challenge($user)->objectives;
    }

    public static function startConfirmed(User $user): bool
    {
        return DB::table('challenge_participations')->where('user_id', $user->id)
            ->where('challenge_id', self::challenge($user)->id)->whereNotNull('start_date_confirmed_at')->exists();
    }

    public static function needsStartChoice(User $user): bool
    {
        return $user->isAthlete() && $user->athleteProfile?->goals_set_at
            && ! self::startConfirmed($user) && ! $user->checkins()->exists();
    }

    public static function summary(?User $user = null): array
    {
        $challenge = self::challenge($user);
        $start = self::startDate($user);

        return ['id' => $challenge->id, 'name' => $challenge->name, 'start_date' => $start,
            'end_date' => Carbon::parse($start)->addDays($challenge->duration_days - 1)->toDateString(),
            'duration_days' => $challenge->duration_days, 'objectives' => $challenge->objectives,
            'default_start_date' => $challenge->default_start_date->toDateString()];
    }
}

<?php

namespace App\Support;

use App\Models\User;

class ReleaseWelcome
{
    public static function available(?User $user): bool
    {
        if (! $user || ! $user->isAthlete() || ! $user->hasChallengeAccess()) {
            return false;
        }

        $access = new ChallengeAccess;

        return $access->pageOpen('dias') && $access->pageOpen('alimentacao') && $access->pageOpen('nutri');
    }
}

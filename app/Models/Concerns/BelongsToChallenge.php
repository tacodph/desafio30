<?php

namespace App\Models\Concerns;

use App\Models\User;
use App\Support\ChallengeContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToChallenge
{
    protected static function bootBelongsToChallenge(): void
    {
        static::addGlobalScope('challenge', fn (Builder $query) => $query->where($query->getModel()->qualifyColumn('challenge_id'), ChallengeContext::challenge()->id));
        static::creating(function ($model) {
            $model->challenge_id ??= ChallengeContext::challenge($model->user_id ? User::find($model->user_id) : null)->id;
        });
    }
}

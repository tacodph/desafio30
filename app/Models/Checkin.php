<?php

namespace App\Models;

use App\Models\Concerns\BelongsToChallenge;
use App\Support\ChallengeContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Checkin extends Model
{
    use BelongsToChallenge;

    public function save(array $options = [])
    {
        return DB::transaction(function () use ($options) {
            $owner = User::lockForUpdate()->findOrFail($this->user_id);
            $challengeId = $this->challenge_id ?? ChallengeContext::challenge($owner)->id;
            Challenge::lockForUpdate()->findOrFail($challengeId);

            return parent::save($options);
        });
    }

    protected $fillable = [
        'challenge_id',
        'user_id',
        'day',
        'checked_in',
        'movement',
        'water',
        'sleep',
        'strength',
        'skill',
        'plus_count',
        'notes',
        'details',
        'workout_photo',
        'meal_photo',
        'meal_comment',
    ];

    protected function casts(): array
    {
        return [
            'checked_in' => 'boolean',
            'movement' => 'boolean',
            'water' => 'boolean',
            'sleep' => 'boolean',
            'strength' => 'boolean',
            'skill' => 'boolean',
            'plus_count' => 'integer',
            'notes' => 'array',
            'details' => 'array',
            'meal_photos' => 'array',
            'reactions' => 'array',
        ];
    }

    public function mealPhotoPaths(): array
    {
        return $this->meal_photo
            ? [$this->meal_photo, ...($this->meal_photos ?? [])]
            : ($this->meal_photos ?? []);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkin extends Model
{
    protected $fillable = [
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

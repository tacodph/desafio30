<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AthleteProfile extends Model
{
    protected $fillable = [
        'user_id',
        'movement_level',
        'movement_minutes',
        'water_liters',
        'sleep_goal',
        'skill',
        'strength_goal',
        'test_1',
        'test_15',
        'test_30',
        'test_45',
        'penalty',
        'penalty_donated_on',
        'penalty_note',
        'weekly_skills',
        'shopping_lists',
        'weight',
        'waist',
        'abdomen',
        'hip',
        'photo',
        'welcomed_at',
        'goals_set_at',
    ];

    protected function casts(): array
    {
        return [
            'penalty' => 'boolean',
            'penalty_donated_on' => 'date',
            'movement_minutes' => 'integer',
            'water_liters' => 'decimal:1',
            'weekly_skills' => 'array',
            'shopping_lists' => 'array',
            'weight' => 'decimal:1',
            'waist' => 'decimal:1',
            'abdomen' => 'decimal:1',
            'hip' => 'decimal:1',
            'welcomed_at' => 'datetime',
            'start_photos' => 'array',
            'goals_set_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

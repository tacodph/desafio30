<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Achievement extends Model
{
    use \App\Models\Concerns\BelongsToChallenge;
    protected $fillable = [
        'challenge_id',
        'user_id',
        'achieved_on',
        'type',
        'detail',
        'emotion',
        'comment',
        'nutritionist',
    ];

    protected function casts(): array
    {
        return [
            'achieved_on' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

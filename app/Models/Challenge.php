<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    public const OBJECTIVES = ['movement' => 'Treino', 'water' => 'Hidratação', 'sleep' => 'Sono', 'strength' => 'Alimentação', 'skill' => 'Habilidade', 'plus' => 'Refeição luxo'];

    protected $fillable = ['name', 'default_start_date', 'duration_days', 'objectives', 'is_default', 'accepting_participants'];

    protected function casts(): array
    {
        return ['default_start_date' => 'date', 'duration_days' => 'integer', 'objectives' => 'array', 'is_default' => 'boolean', 'accepting_participants' => 'boolean'];
    }

    public function getDefaultEndDateAttribute(): string
    {
        return $this->default_start_date->copy()->addDays($this->duration_days - 1)->toDateString();
    }
}

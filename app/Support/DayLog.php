<?php

namespace App\Support;

use App\Models\AthleteProfile;

/**
 * Detalhes lançados em cada dia (treino, água, sono...) e a regra que transforma
 * esses detalhes nos objetivos cumpridos do dia.
 */
class DayLog
{
    public const WORKOUT_TYPES = [
        'Musculação', 'Corrida', 'Caminhada', 'Bike', 'Crossfit', 'Funcional',
        'Natação', 'Futebol', 'Yoga', 'Dança', 'Outro',
    ];

    public const SLEEP_HOURS = 7;

    /** Objetivos que podem ser contados manualmente quando a meta não é batida. */
    public const MANUAL_KEYS = ['movement', 'water', 'sleep', 'strength', 'skill'];

    public const FOOD_KEYS = ['no_alcohol', 'no_fast_food', 'no_sweet', 'luxury'];

    /** Referência oficial do desafio: 35 ml de água por kg de peso corporal. */
    public const WATER_ML_PER_KG = 35;

    public static function waterMinimumLiters(float|string|null $weight): ?float
    {
        $kg = (float) $weight;

        return $kg > 0 ? round($kg * self::WATER_ML_PER_KG / 1000, 1) : null;
    }

    /** @return array{movement_minutes: int, water_ml: int, sleep_hours: int} */
    public static function goals(?AthleteProfile $profile): array
    {
        $minutes = (int) ($profile?->movement_minutes ?? 0);

        if ($minutes <= 0) {
            $minutes = match ($profile?->movement_level) {
                'iniciante' => 20,
                'intermediario' => 30,
                default => 45,
            };
        }

        $liters = (float) ($profile?->water_liters ?? 0);
        $liters = $liters > 0 ? $liters : (self::waterMinimumLiters($profile?->weight) ?? 2.5);

        return [
            'movement_minutes' => $minutes,
            'water_ml' => (int) round($liters * 1000),
            'sleep_hours' => self::SLEEP_HOURS,
        ];
    }

    /** @return array<string, mixed> */
    public static function normalize(?array $details): array
    {
        $details ??= [];

        $workouts = collect($details['workouts'] ?? [])
            ->filter(fn ($item) => is_array($item) && is_string($item['type'] ?? null) && filled($item['type']))
            ->map(fn (array $item) => [
                'type' => trim($item['type']),
                'minutes' => min(600, (int) ($item['minutes'] ?? 0)),
            ])
            ->values()
            ->all();

        $food = [];
        $manual = [];

        foreach (self::FOOD_KEYS as $key) {
            $food[$key] = (bool) ($details['food'][$key] ?? false);
        }

        foreach (self::MANUAL_KEYS as $key) {
            $manual[$key] = (bool) ($details['manual'][$key] ?? false);
        }

        return [
            'workouts' => $workouts,
            'water_ml' => max(0, min(10000, (int) ($details['water_ml'] ?? 0))),
            'sleep_hours' => max(0, min(24, round((float) ($details['sleep_hours'] ?? 0), 1))),
            'food' => $food,
            'skill' => trim((string) ($details['skill'] ?? '')),
            'manual' => $manual,
        ];
    }

    /**
     * Objetivos cumpridos: meta batida OU contados manualmente.
     *
     * @return array{movement: bool, water: bool, sleep: bool, strength: bool, skill: bool}
     */
    public static function achieved(array $details, ?AthleteProfile $profile): array
    {
        $details = self::normalize($details);
        $goals = self::goals($profile);
        $food = $details['food'];
        $manual = $details['manual'];

        return [
            'movement' => array_sum(array_column($details['workouts'], 'minutes')) >= $goals['movement_minutes'] || $manual['movement'],
            'water' => $details['water_ml'] >= $goals['water_ml'] || $manual['water'],
            'sleep' => $details['sleep_hours'] >= $goals['sleep_hours'] || $manual['sleep'],
            'strength' => ($food['no_alcohol'] && $food['no_fast_food'] && $food['no_sweet']) || $food['luxury'] || $manual['strength'],
            'skill' => $details['skill'] !== '' || $manual['skill'],
        ];
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ChallengeAccess
{
    public const TIMEZONE = 'America/Sao_Paulo';

    public const FEATURES = [
        'dias' => 'Página de dias',
        'conquistas' => 'Página de conquistas',
        'resumo' => 'Página de resumo',
        'entries' => 'Lançamentos e fotos dos dias',
    ];

    private ?array $settings = null;

    public function modes(): array
    {
        return $this->settings ??= array_replace(
            array_fill_keys(array_keys(self::FEATURES), 'calendar'),
            Schema::hasTable('challenge_access_settings')
                ? DB::table('challenge_access_settings')->pluck('mode', 'feature')->all()
                : [],
        );
    }

    public function releaseDate(): Carbon
    {
        return Carbon::parse(ChallengeSheet::START, self::TIMEZONE)->subDay()->startOfDay();
    }

    public function pageOpen(string $feature): bool
    {
        return match ($this->modes()[$feature] ?? 'closed') {
            'open' => true,
            'calendar' => Carbon::now(self::TIMEZONE)->gte($this->releaseDate()),
            default => false,
        };
    }

    public function dayEditable(int $day): bool
    {
        if ($day < 1 || $day > ChallengeSheet::TOTAL_DAYS || ! $this->pageOpen('dias')) {
            return false;
        }

        return match ($this->modes()['entries']) {
            'open' => true,
            'calendar' => Carbon::parse(ChallengeSheet::dateForDay($day), self::TIMEZONE)
                ->isSameDay(Carbon::now(self::TIMEZONE)),
            default => false,
        };
    }

    public function ensureDayEditable(int $day): void
    {
        if (! $this->dayEditable($day)) {
            throw ValidationException::withMessages([
                'days' => 'Este dia está bloqueado. Os lançamentos e as fotos só podem ser alterados na data correspondente, pelo horário de Brasília.',
            ]);
        }
    }

    public function payload(): array
    {
        return [
            'releaseDate' => $this->releaseDate()->format('d/m/Y'),
            'timezone' => self::TIMEZONE,
            'pages' => collect(['dias', 'conquistas', 'resumo'])
                ->mapWithKeys(fn (string $key) => [$key => $this->pageOpen($key)])->all(),
            'editableDays' => array_values(array_filter(
                range(1, ChallengeSheet::TOTAL_DAYS), fn (int $day) => $this->dayEditable($day),
            )),
            'entryMode' => $this->modes()['entries'],
        ];
    }
}

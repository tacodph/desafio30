<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ChallengeAccess
{
    public const TIMEZONE = 'America/Sao_Paulo';

    public static function sevenDayContentIsReleased(): bool
    {
        return now()->gte(Carbon::parse(config('desafio.seven_day_content_releases_at'), self::TIMEZONE));
    }

    public static function registrationIsClosed(): bool
    {
        return now()->gte(Carbon::parse(config('desafio.registration_closes_at'), self::TIMEZONE));
    }

    public const MODES = ['calendar', 'manual', 'closed'];

    public const FEATURES = [
        'dias' => 'Página de dias',
        'conquistas' => 'Página de conquistas',
        'resumo' => 'Página de resumo',
        'entries' => 'Lançamentos e fotos dos dias',
        'alimentacao' => 'Alimentação',
        'consultation_meals' => 'Sugestões de refeições reais - Jantar',
        'shopping' => 'Lista de compras inteligente',
        'nutri' => 'Cantinho da Nutri',
    ];

    public const PAGE_FEATURES = ['dias', 'conquistas', 'resumo', 'shopping', 'alimentacao', 'nutri', 'consultation_meals'];

    private ?array $settings = null;

    private ?Request $settingsRequest = null;

    public function settingsTable(): string
    {
        return ChallengeContext::challenge()->id === 1 ? 'challenge_access_settings' : 'challenge_feature_settings';
    }

    public function modes(): array
    {
        // Um controller reutilizado não deve manter liberações de outra requisição.
        $currentRequest = request();
        if ($this->settingsRequest !== $currentRequest) {
            $this->settingsRequest = $currentRequest;
            $this->settings = null;
        }

        return $this->settings ??= array_replace(
            array_fill_keys(array_keys(self::FEATURES), 'calendar'),
            Schema::hasTable($this->settingsTable())
                ? DB::table($this->settingsTable())->when($this->settingsTable() === 'challenge_feature_settings',
                    fn ($query) => $query->where('challenge_id', ChallengeContext::challenge()->id))->pluck('mode', 'feature')
                    ->map(fn ($mode) => in_array($mode, self::MODES, true) ? $mode : 'calendar')->all()
                : [],
        );
    }

    public function releaseDate(): Carbon
    {
        return Carbon::parse(ChallengeSheet::startDate(), self::TIMEZONE)->subDay()->startOfDay();
    }

    public function pageOpen(string $feature): bool
    {
        if ($feature === 'consultation_meals') {
            return request()->user()?->canViewConsultationMeals() ?? false;
        }

        if ($feature === 'shopping' && ! $this->pageOpen('alimentacao')) {
            return false;
        }

        return match ($this->modes()[$feature] ?? 'closed') {
            'calendar' => Carbon::now(self::TIMEZONE)->gte($this->releaseDate())
                || (bool) request()->user()?->challenge_early_access,
            'manual' => true,
            default => false,
        };
    }

    public function dayEditable(int $day): bool
    {
        if ($day < 1 || $day > ChallengeSheet::totalDays() || ! $this->pageOpen('dias')) {
            return false;
        }

        $start = Carbon::parse(ChallengeSheet::dateForDay($day), self::TIMEZONE)->startOfDay();
        $deadline = $start->copy()->addDay()->setTime(23, 54);
        $now = Carbon::now(self::TIMEZONE);

        return match ($this->modes()['entries']) {
            'calendar' => $now->gte($start) && $now->lt($deadline),
            'manual' => true,
            default => false,
        };
    }

    public function ensureDayEditable(int $day): void
    {
        if (! $this->dayEditable($day)) {
            throw ValidationException::withMessages([
                'days' => 'Este dia está bloqueado. Os lançamentos e as fotos podem ser alterados a partir da data correspondente até 23h53 do dia seguinte, pelo horário de Brasília.',
            ]);
        }
    }

    public function payload(): array
    {
        return [
            'releaseDate' => $this->releaseDate()->format('d/m/Y'),
            'timezone' => self::TIMEZONE,
            'pages' => collect(self::PAGE_FEATURES)
                ->mapWithKeys(fn (string $key) => [$key => $this->pageOpen($key)])->all(),
            'editableDays' => array_values(array_filter(
                range(1, ChallengeSheet::totalDays()), fn (int $day) => $this->dayEditable($day),
            )),
            'entryMode' => $this->modes()['entries'],
        ];
    }
}

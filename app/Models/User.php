<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Support\ChallengeAccess;
use App\Support\ChallengeContext;
use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $user->active_challenge_id ??= Challenge::where('is_default', true)->value('id');
        });
        static::created(function (User $user) {
            if ($user->active_challenge_id) {
                $challenge = Challenge::findOrFail($user->active_challenge_id);
                DB::table('challenge_participations')->insertOrIgnore([
                    'user_id' => $user->id, 'challenge_id' => $challenge->id,
                    'start_date' => $challenge->default_start_date->toDateString(), 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'user_type_id',
        'avatar',
        'asaas_customer_id',
        'challenge_paid_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
        'asaas_attestation_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'challenge_paid_at' => 'datetime',
            'challenge_early_access' => 'boolean',
            'welcome_acknowledged_at' => 'datetime',
            'first_challenge_access_at' => 'datetime',
            'release_welcome_acknowledged_at' => 'datetime',
            'challenge_courtesy_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify((new ResetPassword($token))->locale('pt_BR'));
    }

    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }

    public function athleteProfile(): HasOne
    {
        return $this->hasOne(AthleteProfile::class)->withoutGlobalScope('challenge')
            ->where('athlete_profiles.challenge_id', ChallengeContext::challenge($this)->id);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ChallengePayment::class);
    }

    public function challengeAccessType(): BelongsTo
    {
        return $this->belongsTo(ChallengeAccessType::class, 'challenge_access_type_code', 'code');
    }

    public function syncChallengeAccessType(bool $external, ?bool $attested = null): void
    {
        $attested ??= $this->hasAsaasAttestation();
        $confirmedByAsaas = $this->challenge_paid_at !== null && $this->payments()
            ->whereNotNull('asaas_payment_id')->whereIn('status', ChallengePayment::PAID_STATUSES)->exists();

        $this->challenge_access_type_code = match (true) {
            $external => ChallengeAccessType::EXTERNAL,
            $attested => $confirmedByAsaas ? ChallengeAccessType::ASAAS : ChallengeAccessType::ASAAS_ATTESTED,
            $this->isCourtesy() => ChallengeAccessType::COURTESY,
            $this->challenge_paid_at === null => null,
            $confirmedByAsaas => ChallengeAccessType::ASAAS,
            default => ChallengeAccessType::LEGACY,
        };
    }

    public function hasExternalChallengePayment(): bool
    {
        return $this->challenge_access_type_code === ChallengeAccessType::EXTERNAL;
    }

    public function hasAsaasAttestation(): bool
    {
        return $this->challenge_access_type_code === ChallengeAccessType::ASAAS_ATTESTED;
    }

    public function scopeWithPendingChallengePayment(Builder $query): Builder
    {
        return $query->where('user_type_id', UserType::idFor(UserRole::Atleta))
            ->whereNull('challenge_paid_at')
            ->whereNull('challenge_courtesy_at')
            ->where(fn (Builder $query) => $query->whereNull('challenge_access_type_code')
                ->orWhereNotIn('challenge_access_type_code', [ChallengeAccessType::EXTERNAL, ChallengeAccessType::ASAAS_ATTESTED]))
            ->whereHas('payments', fn (Builder $payments) => $payments
                ->where('status', ChallengePayment::PENDING)
                ->whereNotNull('asaas_payment_id'));
    }

    public function isAthlete(): bool
    {
        return $this->user_type_id === UserType::idFor(UserRole::Atleta);
    }

    public function canViewConsultationMeals(): bool
    {
        if ($this->userType?->name === UserRole::Administrador->value) {
            return true;
        }

        $access = app(ChallengeAccess::class);
        if (! $this->isAthlete() || ! $access->pageOpen('alimentacao')) {
            return false;
        }

        return match ($access->modes()['consultation_meals']) {
            'manual' => true,
            'calendar' => ChallengeAccess::sevenDayContentIsReleased(),
            default => false,
        };
    }

    public function hasPaidChallenge(): bool
    {
        return $this->challenge_paid_at !== null || $this->hasExternalChallengePayment();
    }

    public function isCourtesy(): bool
    {
        return $this->challenge_courtesy_at !== null;
    }

    public function hasChallengeAccess(): bool
    {
        return $this->hasPaidChallenge() || $this->isCourtesy() || $this->hasAsaasAttestation();
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class)->withoutGlobalScope('challenge')
            ->where('checkins.challenge_id', ChallengeContext::challenge($this)->id);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class)->withoutGlobalScope('challenge')
            ->where('achievements.challenge_id', ChallengeContext::challenge($this)->id);
    }
}

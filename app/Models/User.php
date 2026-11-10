<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
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
        return $this->hasOne(AthleteProfile::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ChallengePayment::class);
    }

    public function challengeAccessType(): BelongsTo
    {
        return $this->belongsTo(ChallengeAccessType::class, 'challenge_access_type_code', 'code');
    }

    public function syncChallengeAccessType(bool $external): void
    {
        $this->challenge_access_type_code = match (true) {
            $external => ChallengeAccessType::EXTERNAL,
            $this->isCourtesy() => ChallengeAccessType::COURTESY,
            $this->challenge_paid_at === null => null,
            $this->payments()->whereNotNull('asaas_payment_id')->whereIn('status', ChallengePayment::PAID_STATUSES)->exists() => ChallengeAccessType::ASAAS,
            default => ChallengeAccessType::LEGACY,
        };
    }

    public function hasExternalChallengePayment(): bool
    {
        return $this->challenge_access_type_code === ChallengeAccessType::EXTERNAL;
    }

    public function scopeWithPendingChallengePayment(Builder $query): Builder
    {
        return $query->where('user_type_id', UserType::idFor(UserRole::Atleta))
            ->whereNull('challenge_paid_at')
            ->whereNull('challenge_courtesy_at')
            ->where(fn (Builder $query) => $query->whereNull('challenge_access_type_code')
                ->orWhere('challenge_access_type_code', '<>', ChallengeAccessType::EXTERNAL))
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
        return $this->userType?->name === UserRole::Administrador->value
            || ($this->isAthlete() && $this->first_challenge_access_at?->copy()->addDays(7)->lte(now()) === true);
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
        return $this->hasPaidChallenge() || $this->isCourtesy();
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }
}

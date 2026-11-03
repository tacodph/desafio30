<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
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
            'challenge_courtesy_at' => 'datetime',
            'password' => 'hashed',
        ];
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

    public function isAthlete(): bool
    {
        return $this->user_type_id === UserType::idFor(UserRole::Atleta);
    }

    public function hasPaidChallenge(): bool
    {
        return $this->challenge_paid_at !== null;
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

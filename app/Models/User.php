<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
        protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
        ];
    }

    /**
     * Get the reservations for the user.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the upcoming reservations for the user.
     */
    public function upcomingReservations()
    {
        return $this->hasMany(Reservation::class)
            ->whereHas('event', function ($query) {
                $query->where('starts_at', '>=', now());
            });
    }

    /**
     * Get the past reservations for the user.
     */
    public function pastReservations()
    {
        return $this->hasMany(Reservation::class)
            ->whereHas('event', function ($query) {
                $query->where('ends_at', '<', now());
            });
    }

    /**
     * Check if the user has reserved for a specific event.
     */
    public function hasReservedEvent(int $eventId): bool
    {
        return $this->reservations()
            ->where('event_id', $eventId)
            ->exists();
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'guests_count',
    ];

    /**
     * Get the event that owns the reservation.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user that owns the reservation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include reservations for a specific event.
     */
    public function scopeForEvent(Builder $query, $eventId): void
    {
        $query->where('event_id', $eventId);
    }

    /**
     * Scope a query to only include reservations for the authenticated user.
     */
    public function scopeForCurrentUser(Builder $query): void
    {
        $query->where('user_id', auth()->id());
    }

    /**
     * Scope a query to only include recent reservations.
     */
    public function scopeRecent(Builder $query, int $days = 7): void
    {
        $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to only include reservations for upcoming events.
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->whereHas('event', function (Builder $query) {
            $query->where('starts_at', '>=', now());
        });
    }

    /**
     * Scope a query to only include reservations for past events.
     */
    public function scopePast(Builder $query): void
    {
        $query->whereHas('event', function (Builder $query) {
            $query->where('ends_at', '<', now());
        });
    }

    /**
     * Check if the reservation is for an upcoming event.
     */
    public function isUpcoming(): bool
    {
        return $this->event->starts_at >= now();
    }

    /**
     * Check if the reservation can be cancelled.
     * Reservations can be cancelled if the event hasn't started yet.
     */
    public function canBeCancelled(): bool
    {
        return $this->event->starts_at > now();
    }

    /**
     * حساب إجمالي عدد الضيوف (بما في ذلك صاحب الحجز)
     */
    protected function totalAttendees(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->guests_count + 1; // +1 لصاحب الحجز
            }
        );
    }

    /**
     * تنسيق تاريخ الحجز
     */
    protected function formattedCreatedAt(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->created_at->format('d M Y H:i');
            }
        );
    }
}



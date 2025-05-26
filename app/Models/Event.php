<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Event extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title',
        'description',
        'event_type_id',
        'location_id',
        'starts_at',
        'ends_at',
        'user_id'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = ['starts_at', 'ends_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     * Automatically sets the user_id to the authenticated user when creating a new event.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($event) {
            // Set user_id if not provided
            if (!$event->user_id && auth()->check()) {
                $event->user_id = auth()->id();
            }
        });
    }

    /**
     * Get the user that owns the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event type of the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function eventType()
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    /**
     * Get the location of the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the reservations for the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the latest reservation for the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestReservation()
    {
        return $this->hasOne(Reservation::class)->latestOfMany();
    }

    /**
     * Get the oldest reservation for the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function oldestReservation()
    {
        return $this->hasOne(Reservation::class)->oldestOfMany();
    }

    /**
     * Get the reservation with the most guests.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function largestReservation()
    {
        return $this->hasOne(Reservation::class)->ofMany('guests_count', 'max');
    }

    /**
     * Check if the event is fully booked.
     * Assuming a maximum capacity of 50 attendees.
     *
     * @return bool
     */
    public function isFullyBooked(): bool
    {
        return $this->reservations()->sum('guests_count') >= 50;
    }

    /**
     * Get the number of available spots.
     * Assuming a maximum capacity of 50 attendees.
     *
     * @return int
     */
    public function availableSpots(): int
    {
        $reserved = $this->reservations()->sum('guests_count');
        return max(0, 50 - $reserved);
    }

    /**
     * Get all images for the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Get the cover image for the event.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function coverImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable')
            ->where('is_cover', true)
            ->latestOfMany();
    }

    /**
     * Scope a query to only include upcoming events.
     * Filters events where start date is in the future.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where('starts_at', '>=', now());
    }

    /**
     * Scope a query to only include past events.
     * Filters events where end date is in the past.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopePast(Builder $query): void
    {
        $query->where('ends_at', '<', now());
    }

    /**
     * Scope a query to only include events of a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $typeId
     * @return void
     */
    public function scopeOfType(Builder $query, $typeId): void
    {
        $query->where('event_type_id', $typeId);
    }

    /**
     * Scope a query to only include events at a specific location.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $locationId
     * @return void
     */
    public function scopeAtLocation(Builder $query, $locationId): void
    {
        $query->where('location_id', $locationId);
    }

    /**
     * Check if the event is upcoming.
     * An event is upcoming if its start date is in the future.
     *
     * @return bool
     */
    public function isUpcoming(): bool
    {
        return $this->starts_at >= now();
    }

    /**
     * Check if the event is ongoing.
     * An event is ongoing if current time is between start and end dates.
     *
     * @return bool
     */
    public function isOngoing(): bool
    {
        $now = now();
        return $this->starts_at <= $now && $this->ends_at >= $now;
    }

    /**
     * Check if the event is past.
     * An event is past if its end date is in the past.
     *
     * @return bool
     */
    public function isPast(): bool
    {
        return $this->ends_at < now();
    }

    /**
     * Get the event's status.
     * Returns 'upcoming', 'ongoing', or 'past' based on the event's dates.
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        if ($this->isUpcoming()) {
            return 'upcoming';
        } elseif ($this->isOngoing()) {
            return 'ongoing';
        } else {
            return 'past';
        }
    }

    /**
     * Convert event title: clean text and capitalize first letter of each word.
     * Accessor and mutator for the title attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucwords($value),
            set: fn (string $value) => trim($value)
        );
    }

    /**
     * Calculate event duration in readable format.
     * Returns a human-readable time difference between start and end dates.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function duration(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->starts_at->diffForHumans($this->ends_at, true);
            }
        );
    }

    /**
     * Format event date in an easy-to-read way.
     * Formats dates differently if they're on the same day or different days.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function formattedDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                // If dates are on the same day, show only time
                if ($this->starts_at->isSameDay($this->ends_at)) {
                    return $this->starts_at->format('d M Y') . ' | ' .
                        $this->starts_at->format('H:i') . ' - ' .
                        $this->ends_at->format('H:i');
                }

                // If on different days
                return $this->starts_at->format('d M Y H:i') . ' - ' .
                    $this->ends_at->format('d M Y H:i');
            }
        );
    }
}








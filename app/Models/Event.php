<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'event_type_id',
        'location_id',
        'starts_at',
        'ends_at',
        'user_id'
    ];

    protected $dates = ['starts_at', 'ends_at'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
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
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event type of the event.
     */
    public function eventType()
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    /**
     * Get the location of the event.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the reservations for the event.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the latest reservation for the event.
     */
    public function latestReservation()
    {
        return $this->hasOne(Reservation::class)->latestOfMany();
    }

    /**
     * Get the oldest reservation for the event.
     */
    public function oldestReservation()
    {
        return $this->hasOne(Reservation::class)->oldestOfMany();
    }

    /**
     * Get the reservation with the most guests.
     */
    public function largestReservation()
    {
        return $this->hasOne(Reservation::class)->ofMany('guests_count', 'max');
    }

    /**
     * Check if the event is fully booked.
     * Assuming a maximum capacity of 50 attendees.
     */
    public function isFullyBooked(): bool
    {
        return $this->reservations()->sum('guests_count') >= 50;
    }

    /**
     * Get the number of available spots.
     * Assuming a maximum capacity of 50 attendees.
     */
    public function availableSpots(): int
    {
        $reserved = $this->reservations()->sum('guests_count');
        return max(0, 50 - $reserved);
    }

    /**
     * Get all images for the event.
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Get the cover image for the event.
     */
    public function coverImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable')
            ->where('is_cover', true)
            ->latestOfMany();
    }

    /**
     * Scope a query to only include upcoming events.
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where('starts_at', '>=', now());
    }

    /**
     * Scope a query to only include past events.
     */
    public function scopePast(Builder $query): void
    {
        $query->where('ends_at', '<', now());
    }

    /**
     * Scope a query to only include events of a specific type.
     */
    public function scopeOfType(Builder $query, $typeId): void
    {
        $query->where('event_type_id', $typeId);
    }

    /**
     * Scope a query to only include events at a specific location.
     */
    public function scopeAtLocation(Builder $query, $locationId): void
    {
        $query->where('location_id', $locationId);
    }

    /**
     * Check if the event is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->starts_at >= now();
    }

    /**
     * Check if the event is ongoing.
     */
    public function isOngoing(): bool
    {
        $now = now();
        return $this->starts_at <= $now && $this->ends_at >= $now;
    }

    /**
     * Check if the event is past.
     */
    public function isPast(): bool
    {
        return $this->ends_at < now();
    }

    /**
     * Get the event's status.
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
     * تحويل عنوان الفعالية: تنظيف النص وجعل أول حرف من كل كلمة كبير
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucwords($value),
            set: fn (string $value) => trim($value)
        );
    }

    /**
     * حساب مدة الفعالية بصيغة مقروءة
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
     * تنسيق تاريخ الفعالية بطريقة سهلة القراءة
     */
    protected function formattedDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                // إذا كان التاريخ في نفس اليوم، أظهر الوقت فقط
                if ($this->starts_at->isSameDay($this->ends_at)) {
                    return $this->starts_at->format('d M Y') . ' | ' .
                        $this->starts_at->format('H:i') . ' - ' .
                        $this->ends_at->format('H:i');
                }

                // إذا كان في أيام مختلفة
                return $this->starts_at->format('d M Y H:i') . ' - ' .
                    $this->ends_at->format('d M Y H:i');
            }
        );
    }
}





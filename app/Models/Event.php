<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Builder;

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
}

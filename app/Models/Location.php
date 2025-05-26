<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Location extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['name', 'latitude', 'longitude'];
    
    /**
     * Get the events for this location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events() {
        return $this->hasMany(Event::class);
    }

    /**
     * Get all images for this location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images() {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Get the cover image for this location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function coverImage()
    {
        return $this->morphOne(Image::class, 'imageable')
                    ->where('is_cover', true)
                    ->latestOfMany();
    }

    /**
     * Format location name.
     * Capitalizes the first letter of the name and trims whitespace.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => trim($value)
        );
    }

    /**
     * Create Google Maps link from coordinates.
     * Generates a Google Maps URL using the location's latitude and longitude.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function googleMapsUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->latitude && $this->longitude) {
                    return "https://maps.google.com/?q={$this->latitude},{$this->longitude}";
                }
                return null;
            }
        );
    }
}




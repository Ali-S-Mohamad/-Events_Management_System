<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Location extends Model
{
    protected $fillable = ['name', 'latitude', 'longitude'];
    public function events() {
        return $this->hasMany(Event::class);
    }

    public function images() {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function coverImage()
    {
        return $this->morphOne(Image::class, 'imageable')
                    ->where('is_cover', true)
                    ->latestOfMany();
    }

    /**
     * تنسيق اسم الموقع
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
            set: fn (string $value) => trim($value)
        );
    }

    /**
     * إنشاء رابط خرائط جوجل من الإحداثيات
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


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}

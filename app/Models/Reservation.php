<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{

    protected $fillable = ['event_id', 'user_id', 'guests_count'];
    protected $dates = ['starts_at', 'ends_at'];

    public function event() {
        return $this->belongsTo(Event::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}

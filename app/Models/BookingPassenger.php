<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPassenger extends Model
{
    public $timestamps = false;

    protected $table = 'booking_passengers';

    protected $guarded = ['id', 'booking_id'];

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
}

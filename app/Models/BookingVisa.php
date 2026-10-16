<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingVisa extends Model
{
    public $timestamps = false;

    protected $table = 'booking_visas';

    protected $guarded = ['id', 'booking_id'];

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
}

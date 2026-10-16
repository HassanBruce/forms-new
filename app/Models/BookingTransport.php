<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingTransport extends Model
{
    public $timestamps = false;

    protected $table = 'booking_transports';

    protected $guarded = ['id', 'booking_id'];

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
}

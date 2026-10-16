<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPrice extends Model
{
    public $timestamps = false;

    protected $table = 'booking_prices';

    protected $guarded = ['id', 'booking_id'];

    public function total(): float
    {
        return round($this->quantity * (float) $this->unit_price, 2);
    }

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingHotel extends Model
{
    public $timestamps = false;

    protected $table = 'booking_hotels';

    protected $guarded = ['id', 'booking_id'];

    protected $casts = ['check_in' => 'date', 'check_out' => 'date'];

    public function nights(): int
    {
        return ($this->check_in && $this->check_out) ? (int) $this->check_in->diffInDays($this->check_out) : 0;
    }

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
}

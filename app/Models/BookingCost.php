<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCost extends Model
{
    public $timestamps = false;

    protected $table = 'booking_costs';

    protected $guarded = ['id', 'booking_id'];

    public function total(): float
    {
        return round($this->quantity * (float) $this->unit_cost, 2);
    }

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
}

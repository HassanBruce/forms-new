<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHODS = ['bank_transfer' => 'Bank transfer', 'cash' => 'Cash', 'card' => 'Card', 'cheque' => 'Cheque', 'other' => 'Other'];

    protected $guarded = ['id', 'booking_id', 'recorded_by'];

    protected $casts = ['paid_on' => 'date', 'amount' => 'decimal:2'];

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function recorder(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by'); }

    public function methodLabel(): string { return self::METHODS[$this->method] ?? ucfirst($this->method); }
}

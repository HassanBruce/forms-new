<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['trial_ends_at' => 'datetime'];

    public function brands(): HasMany { return $this->hasMany(Brand::class); }
    public function users(): HasMany { return $this->hasMany(User::class); }

    /** Atomically reserve the next booking reference, e.g. TD-10482. */
    public function nextBookingRef(): string
    {
        return DB::transaction(function () {
            $c = static::query()->lockForUpdate()->find($this->id);
            $no = $c->next_booking_no;
            $c->increment('next_booking_no');
            return $c->booking_prefix.'-'.$no;
        });
    }

    public function nextInvoiceNo(): string
    {
        return DB::transaction(function () {
            $c = static::query()->lockForUpdate()->find($this->id);
            $no = $c->next_invoice_no;
            $c->increment('next_invoice_no');
            return 'INV-'.now()->year.'-'.str_pad((string) $no, 4, '0', STR_PAD_LEFT);
        });
    }

    public function money(float|string|null $v): string
    {
        $sym = ['GBP' => '£', 'USD' => '$', 'EUR' => '€', 'PKR' => 'Rs ', 'SAR' => 'SAR '][$this->currency] ?? $this->currency.' ';
        return $sym.number_format((float) $v, 2);
    }
}

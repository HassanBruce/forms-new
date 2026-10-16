<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use BelongsToCompany;

    public const STATUSES = ['draft' => 'Draft', 'waiting' => 'Waiting', 'accepted' => 'Accepted', 'rejected' => 'Rejected'];

    public const STEPS = [1 => 'Passengers', 2 => 'Flight & hotels', 3 => 'Transport & pricing', 4 => 'Profit / loss', 5 => 'Payments'];

    protected $guarded = ['id', 'company_id', 'ref', 'total_sale', 'total_purchase', 'total_paid'];

    protected $casts = [
        'travel_start' => 'date',
        'travel_end' => 'date',
        'invoice_sent_at' => 'datetime',
        'total_sale' => 'decimal:2',
        'total_purchase' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
    public function team(): BelongsTo { return $this->belongsTo(Team::class); }
    public function agent(): BelongsTo { return $this->belongsTo(User::class, 'agent_id'); }
    public function passengers(): HasMany { return $this->hasMany(BookingPassenger::class)->orderBy('position'); }
    public function hotels(): HasMany { return $this->hasMany(BookingHotel::class)->orderBy('id'); }
    public function transports(): HasMany { return $this->hasMany(BookingTransport::class)->orderBy('id'); }
    public function visas(): HasMany { return $this->hasMany(BookingVisa::class)->orderBy('id'); }
    public function prices(): HasMany { return $this->hasMany(BookingPrice::class)->orderBy('id'); }
    public function costs(): HasMany { return $this->hasMany(BookingCost::class)->orderBy('id'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class)->orderBy('paid_on')->orderBy('id'); }

    /** Hierarchy scoping: admin/accounts → company, director → brand, lead → team, agent → own. */
    public function scopeVisibleTo(Builder $q, User $u): Builder
    {
        return match ($u->role) {
            'admin', 'accounts' => $q,
            'director' => $q->where('brand_id', $u->brand_id),
            'team_lead' => $q->where(fn ($w) => $w->where('agent_id', $u->id)->when($u->team_id, fn ($x) => $x->orWhere('team_id', $u->team_id))),
            default => $q->where('agent_id', $u->id),
        };
    }

    /** Recompute the stored totals from line items. Call after any change. */
    public function recalculate(): void
    {
        $this->forceFill([
            'total_sale' => $this->prices()->get()->sum(fn ($p) => $p->total()),
            'total_purchase' => $this->costs()->get()->sum(fn ($c) => $c->total()),
            'total_paid' => (float) $this->payments()->sum('amount'),
        ])->save();
    }

    public function margin(): float { return round((float) $this->total_sale - (float) $this->total_purchase, 2); }

    public function marginPct(): float
    {
        return (float) $this->total_sale > 0 ? round($this->margin() / (float) $this->total_sale * 100, 1) : 0.0;
    }

    public function pending(): float { return round(max(0, (float) $this->total_sale - (float) $this->total_paid), 2); }

    public function paidPct(): float
    {
        return (float) $this->total_sale > 0 ? min(100, round((float) $this->total_paid / (float) $this->total_sale * 100)) : 0;
    }

    public function paymentStatus(): string
    {
        if ((float) $this->total_sale <= 0) return 'none';
        if ((float) $this->total_paid <= 0) return 'unpaid';
        return (float) $this->total_paid >= (float) $this->total_sale ? 'paid' : 'partial';
    }

    public function paxSummary(): string
    {
        $c = $this->passengers->groupBy('type')->map->count();
        $parts = [];
        foreach (['adult' => 'Adult', 'child' => 'Child', 'infant' => 'Infant'] as $k => $l) {
            if ($n = $c->get($k)) $parts[] = $n.' '.($n > 1 ? ($k === 'child' ? 'Children' : $l.'s') : $l);
        }
        return $parts ? implode(' · ', $parts) : 'No passengers yet';
    }

    public function travelRange(): string
    {
        if (! $this->travel_start) return '—';
        $end = $this->travel_end ?: $this->travel_start;
        return $this->travel_start->format('d M').' – '.$end->format('d M Y');
    }

    /** Derive travel dates from hotel stays when not entered explicitly. */
    public function syncTravelDates(): void
    {
        $h = $this->hotels()->get();
        $in = $h->pluck('check_in')->filter()->min();
        $out = $h->pluck('check_out')->filter()->max();
        if ($in) $this->travel_start = $in;
        if ($out) $this->travel_end = $out;
        $this->save();
    }
}

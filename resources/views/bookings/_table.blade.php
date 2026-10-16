@php $c = auth()->user()->company; $compact = $compact ?? false; @endphp
<div class="card overflow-x-auto">
    <table class="w-full min-w-[860px]">
        <thead class="bg-line-soft"><tr>
            <th class="th">Booking</th><th class="th">Lead guest</th>
            @unless($compact)<th class="th">Brand</th>@endunless
            <th class="th">Agent</th><th class="th">Travel</th><th class="th text-right">Total</th>
            @unless($compact)<th class="th">Payment</th>@endunless
            <th class="th">Status</th>
        </tr></thead>
        <tbody>
        @forelse($bookings as $b)
            <tr class="border-t border-line-soft hover:bg-canvas/60">
                <td class="td"><a href="{{ route('bookings.show', $b) }}" class="font-semibold text-brand-600 hover:underline">{{ $b->ref }}</a></td>
                <td class="td"><p class="font-semibold text-ink-900">{{ $b->lead_name ?: 'Untitled draft' }}</p><p class="text-xs text-ink-500">{{ $b->passengers->count() }} pax{{ $b->pnr ? ' · PNR '.$b->pnr : '' }}</p></td>
                @unless($compact)<td class="td"><span class="flex items-center gap-2"><span class="grid size-6 place-items-center rounded-full text-[10px] font-semibold text-white" style="background: {{ $b->brand->color }}">{{ $b->brand->initials() }}</span><span class="text-[13px]">{{ $b->brand->name }}</span></span></td>@endunless
                <td class="td">{{ $b->agent->name ?? '—' }}</td>
                <td class="td whitespace-nowrap">{{ $b->travelRange() }}</td>
                <td class="td text-right font-semibold text-ink-900">{{ $c->money($b->total_sale) }}</td>
                @unless($compact)<td class="td"><p class="text-[13px]">{{ $b->paymentStatus() === 'paid' ? 'Fully paid' : $c->money($b->total_paid).' paid' }}</p>
                    <div class="mt-1.5 h-1.5 w-[100px] rounded bg-line-soft"><div class="h-1.5 rounded {{ $b->paymentStatus() === 'paid' ? 'bg-green-600' : 'bg-sky-600' }}" style="width: {{ $b->paidPct() }}%"></div></div></td>@endunless
                <td class="td"><x-badge :status="$b->status"/></td>
            </tr>
        @empty
            <tr><td colspan="8" class="px-5 py-14 text-center text-sm text-ink-500">No bookings yet. Create your first one with <b>New booking</b>.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

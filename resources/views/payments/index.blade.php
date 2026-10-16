@php $c = auth()->user()->company; @endphp
<x-layouts.app title="Payments">
    <x-page-header title="Payments" sub="Collections across the bookings you can see"/>
    <div class="grid gap-5 sm:grid-cols-3">
        @foreach([['Total invoiced', $totals['invoiced'], 'text-ink-900'], ['Total paid', $totals['paid'], 'text-green-600'], ['Total pending', $totals['pending'], 'text-amber-600']] as [$l, $v, $cls])
            <div class="card p-5"><p class="eyebrow">{{ $l }}</p><p class="mt-1.5 text-[32px] leading-10 font-bold {{ $cls }}">{{ $c->money($v) }}</p></div>
        @endforeach
    </div>
    <div class="grid items-start gap-5 xl:grid-cols-[1fr_360px]">
        <div class="space-y-3">
            <div class="flex gap-2">
                <a href="?show=pending" class="btn btn-sm {{ $filter === 'pending' ? 'btn-primary' : 'btn-secondary' }}">With balance</a>
                <a href="?show=all" class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-secondary' }}">All</a>
            </div>
            <div class="card overflow-x-auto">
                <table class="w-full min-w-[640px]">
                    <thead class="bg-line-soft"><tr><th class="th">Booking</th><th class="th">Customer</th><th class="th text-right">Total</th><th class="th text-right">Paid</th><th class="th text-right">Pending</th><th class="th"></th></tr></thead>
                    <tbody>
                    @forelse($bookings as $b)
                        <tr class="border-t border-line-soft">
                            <td class="td"><a class="font-semibold text-brand-600" href="{{ route('bookings.step', [$b, 5]) }}">{{ $b->ref }}</a><p class="text-xs text-ink-500">{{ $b->brand->name }} · {{ $b->agent->name ?? '' }}</p></td>
                            <td class="td">{{ $b->lead_name }}</td>
                            <td class="td text-right">{{ $c->money($b->total_sale) }}</td>
                            <td class="td text-right text-green-600">{{ $c->money($b->total_paid) }}</td>
                            <td class="td text-right font-semibold text-ink-900">{{ $c->money($b->pending()) }}</td>
                            <td class="td text-right"><a href="{{ route('bookings.step', [$b, 5]) }}" class="btn btn-ghost btn-sm">Record <x-icon name="arrow-right" class="size-4"/></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center text-sm text-ink-500">Nothing outstanding. 🎉</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $bookings->links() }}
        </div>
        <div class="card space-y-3 p-5">
            <h3 class="text-[15px] font-semibold">Recent payments</h3>
            @forelse($recent as $p)
                <a href="{{ route('bookings.step', [$p->booking, 5]) }}" class="flex items-center gap-3 rounded-lg p-1 hover:bg-canvas">
                    <span class="grid size-9 place-items-center rounded-[10px] bg-green-50 text-green-600"><x-icon name="wallet" class="size-[18px]"/></span>
                    <span class="min-w-0 flex-1"><span class="block text-sm font-semibold">{{ $p->booking->ref }} · {{ $p->methodLabel() }}</span><span class="block text-xs text-ink-500">{{ $p->paid_on->format('d M Y') }}{{ $p->reference ? ' · '.$p->reference : '' }}</span></span>
                    <span class="text-sm font-semibold">{{ $c->money($p->amount) }}</span>
                </a>
            @empty <p class="text-sm text-ink-500">No payments yet.</p> @endforelse
        </div>
    </div>
</x-layouts.app>

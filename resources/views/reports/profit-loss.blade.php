@php $c = auth()->user()->company; @endphp
<x-layouts.app title="Profit & Loss">
    <x-page-header title="Profit & Loss" sub="Sale vs supplier purchase for bookings you can see (rejected excluded)">
        <form class="flex flex-wrap items-center gap-2">
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input w-auto">
            <span class="text-ink-400">→</span>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input w-auto">
            <select name="by" class="input w-auto"><option value="agent" @selected($group === 'agent')>By agent</option><option value="brand" @selected($group === 'brand')>By brand</option></select>
            <button class="btn btn-secondary">Apply</button>
        </form>
    </x-page-header>
    <section class="card grid divide-y divide-line p-6 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
        <div class="pb-4 sm:pr-6 sm:pb-0"><p class="eyebrow">Total sale</p><p class="mt-1.5 text-[32px] leading-10 font-bold">{{ $c->money($tot['sale']) }}</p></div>
        <div class="py-4 sm:px-6 sm:py-0"><p class="eyebrow">Total purchase</p><p class="mt-1.5 text-[32px] leading-10 font-bold">{{ $c->money($tot['purchase']) }}</p></div>
        <div class="pt-4 sm:pl-6 sm:pt-0"><p class="eyebrow">Margin</p><p class="mt-1.5 text-[32px] leading-10 font-bold {{ $tot['margin'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $c->money($tot['margin']) }}</p>
            <p class="text-[13px] text-ink-500">{{ $tot['sale'] > 0 ? round($tot['margin'] / $tot['sale'] * 100, 1) : 0 }}% of sale</p></div>
    </section>
    <div class="card overflow-x-auto">
        <table class="w-full min-w-[640px]">
            <thead class="bg-line-soft"><tr><th class="th">{{ $group === 'agent' ? 'Agent' : 'Brand' }}</th><th class="th text-right">Bookings</th><th class="th text-right">Sale</th><th class="th text-right">Purchase</th><th class="th text-right">Margin</th><th class="th text-right">%</th></tr></thead>
            <tbody>
            @forelse($rows as $r)
                <tr class="border-t border-line-soft"><td class="td font-semibold text-ink-900">{{ $r['name'] }}</td><td class="td text-right">{{ $r['count'] }}</td><td class="td text-right">{{ $c->money($r['sale']) }}</td><td class="td text-right">{{ $c->money($r['purchase']) }}</td>
                    <td class="td text-right font-semibold {{ $r['margin'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $c->money($r['margin']) }}</td><td class="td text-right">{{ $r['pct'] }}%</td></tr>
            @empty
                <tr><td colspan="6" class="px-5 py-14 text-center text-sm text-ink-500">No priced bookings in this period.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($bookings->isNotEmpty())
    <h2 class="text-lg font-semibold">Bookings in period</h2>
    <div class="card overflow-x-auto">
        <table class="w-full min-w-[720px]">
            <thead class="bg-line-soft"><tr><th class="th">Booking</th><th class="th">Agent</th><th class="th text-right">Sale</th><th class="th text-right">Purchase</th><th class="th text-right">Margin</th><th class="th">Status</th></tr></thead>
            <tbody>
            @foreach($bookings as $b)
                <tr class="border-t border-line-soft"><td class="td"><a class="font-semibold text-brand-600" href="{{ route('bookings.step', [$b, 4]) }}">{{ $b->ref }}</a> <span class="text-xs text-ink-500">{{ $b->lead_name }}</span></td><td class="td">{{ $b->agent->name ?? '—' }}</td>
                    <td class="td text-right">{{ $c->money($b->total_sale) }}</td><td class="td text-right">{{ $c->money($b->total_purchase) }}</td>
                    <td class="td text-right font-semibold {{ $b->margin() >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $c->money($b->margin()) }}</td><td class="td"><x-badge :status="$b->status"/></td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-layouts.app>

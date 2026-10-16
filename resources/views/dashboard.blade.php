@php $c = auth()->user()->company; $first = explode(' ', auth()->user()->name)[0]; $h = now()->hour; @endphp
<x-layouts.app title="Dashboard">
    <x-page-header :title="($h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening')).', '.$first" sub="Here's how {{ auth()->user()->hasRole('admin','accounts') ? $c->name : (auth()->user()->brand->name ?? $c->name) }} is doing this month.">
        <form class="flex items-center gap-2 rounded-[10px] border border-line bg-white px-3 py-2">
            <x-icon name="calendar" class="size-4 text-ink-500"/>
            <input type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()" class="bg-transparent text-sm font-semibold text-ink-700 outline-none">
        </form>
    </x-page-header>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['Total sales', $c->money($kpi['sales']), $kpi['growth'] !== null ? ($kpi['growth'] >= 0 ? '+' : '').$kpi['growth'].'%' : '—', 'vs last month', 'chart', 'brand'],
            ['Gross margin', $c->money($kpi['margin']), $kpi['margin_pct'].'%', 'of sales', 'wallet', 'success'],
            ['Outstanding', $c->money($kpi['outstanding']), $kpi['outstanding_count'], 'open invoices', 'clock', 'warning'],
            ['Bookings', $kpi['bookings'], $kpi['accepted'], 'accepted', 'briefcase', 'info'],
        ] as [$label, $value, $pill, $pillLabel, $icon, $tone])
            @php $t = ['brand'=>'bg-brand-50 text-brand-600','success'=>'bg-green-50 text-green-600','warning'=>'bg-amber-50 text-amber-600','info'=>'bg-sky-50 text-sky-600'][$tone]; @endphp
            <div class="card space-y-3.5 p-5">
                <div class="flex items-center gap-2.5"><span class="grid size-9 place-items-center rounded-[10px] {{ $t }}"><x-icon :name="$icon" class="size-[18px]"/></span><span class="text-sm font-semibold text-ink-500">{{ $label }}</span></div>
                <p class="text-[32px] leading-10 font-bold tracking-[-.5px]">{{ $value }}</p>
                <p class="flex items-center gap-1.5 text-xs text-ink-500"><span class="rounded-full px-2 py-0.5 font-medium {{ $t }}">{{ $pill }}</span>{{ $pillLabel }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 xl:grid-cols-[1fr_380px]">
        <div class="card space-y-5 p-5">
            <div class="flex items-center gap-4">
                <div class="flex-1"><h3 class="text-[15px] font-semibold">Sales vs purchase</h3><p class="text-[13px] text-ink-500">Last 12 months · {{ $c->currency }}</p></div>
                <span class="flex items-center gap-1.5 text-xs text-ink-500"><i class="size-2.5 rounded-[3px] bg-brand-600"></i>Sales</span>
                <span class="flex items-center gap-1.5 text-xs text-ink-500"><i class="size-2.5 rounded-[3px] bg-brand-100"></i>Purchase</span>
            </div>
            <div class="flex h-[230px] items-end justify-between gap-1">
                @foreach($chart as $m)
                    <div class="flex h-full flex-1 flex-col items-center justify-end gap-2" title="{{ $m['label'] }}: sales {{ $c->money($m['sale']) }}, purchase {{ $c->money($m['purchase']) }}">
                        <div class="flex h-full items-end gap-1">
                            <span class="w-2.5 rounded-t sm:w-3.5 bg-brand-600" style="height: {{ max(1, $m['sale'] / $chartMax * 100) }}%"></span>
                            <span class="w-2.5 rounded-t sm:w-3.5 bg-brand-100" style="height: {{ max(1, $m['purchase'] / $chartMax * 100) }}%"></span>
                        </div>
                        <span class="text-xs text-ink-400">{{ $m['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card space-y-[18px] p-5">
            <div><h3 class="text-[15px] font-semibold">Booking status</h3><p class="text-[13px] text-ink-500">{{ $kpi['bookings'] }} bookings this month</p></div>
            @php $total = max(1, $statusCounts->sum()); @endphp
            @foreach(['accepted' => 'bg-green-600', 'waiting' => 'bg-amber-600', 'rejected' => 'bg-red-600', 'draft' => 'bg-ink-400'] as $s => $bar)
                @php $n = (int) ($statusCounts[$s] ?? 0); @endphp
                <div class="space-y-2">
                    <div class="flex text-sm"><span class="flex-1 font-semibold text-ink-700">{{ ucfirst($s) }}</span><span class="text-[13px] text-ink-500">{{ $n }} · {{ round($n / $total * 100) }}%</span></div>
                    <div class="h-2 rounded bg-line-soft"><div class="h-2 rounded {{ $bar }}" style="width: {{ $n / $total * 100 }}%"></div></div>
                </div>
            @endforeach
            <hr class="border-line">
            <div><p class="text-sm font-semibold">Top agent</p>
                <p class="text-[13px] text-ink-500">@if($topAgent && $topAgent['user']){{ $topAgent['user']->name }} · {{ $c->money($topAgent['sales']) }} sales · {{ $topAgent['count'] }} bookings @else No bookings yet this month @endif</p></div>
        </div>
    </div>

    <div class="flex items-center"><h2 class="flex-1 text-lg font-semibold">Recent bookings</h2><a href="{{ route('bookings.index') }}" class="btn btn-ghost btn-sm">View all <x-icon name="arrow-right" class="size-4"/></a></div>
    @include('bookings._table', ['bookings' => $recent, 'compact' => true])
</x-layouts.app>

@php
    $c = $booking->company;
    $sym = rtrim($c->money(0), '0.');
    $transports = old('transports', $booking->transports->map->only(['vehicle_type','capacity','route','supplier'])->values()->all());
    $visas = old('visas', $booking->visas->map->only(['visa_type','supplier','quantity'])->values()->all());
    $pax = $booking->passengers->groupBy('type')->map->count();
    $defaultPrices = collect(['adult' => 'Adult', 'child' => 'Child', 'infant' => 'Infant'])->filter(fn ($l, $k) => $pax->get($k))->map(fn ($l, $k) => ['pax_type' => $l, 'quantity' => $pax->get($k), 'unit_price' => ''])->values()->all();
    $prices = old('prices', $booking->prices->isNotEmpty() ? $booking->prices->map(fn ($p) => ['pax_type' => $p->pax_type, 'quantity' => $p->quantity, 'unit_price' => (float) $p->unit_price])->values()->all() : $defaultPrices);
@endphp
<x-wizard :booking="$booking" :step="3" :editable="$editable">
    @include('bookings._datalists')
    <section class="card space-y-4 p-6" x-data="repeater(@js($transports), { vehicle_type: '', capacity: '', route: '', supplier: '' }, true)">
        <x-card-head icon="car" title="Transport details" sub="Ground transfers between sectors"/>
        <div x-show="rows.length" class="hidden grid-cols-[160px_90px_1fr_190px_24px] gap-3 rounded-lg bg-line-soft px-3 py-2.5 md:grid">
            @foreach(['Vehicle type','Capacity','Sector / route','Supplier',''] as $h)<span class="eyebrow">{{ $h }}</span>@endforeach
        </div>
        <template x-for="(r, i) in rows" :key="r._k">
            <div class="grid grid-cols-2 items-center gap-3 md:grid-cols-[160px_90px_1fr_190px_24px] md:px-3">
                <input class="input input-sm" list="dl-vehicle" :name="`transports[${i}][vehicle_type]`" x-model="r.vehicle_type" placeholder="GMC Yukon" required>
                <input class="input input-sm" type="number" min="1" :name="`transports[${i}][capacity]`" x-model="r.capacity" placeholder="7">
                <input class="input input-sm col-span-2 md:col-span-1" :name="`transports[${i}][route]`" x-model="r.route" placeholder="Jeddah Airport → Makkah Hotel">
                <input class="input input-sm" list="dl-transport" :name="`transports[${i}][supplier]`" x-model="r.supplier" placeholder="Supplier">
                <button type="button" @click="remove(i)" class="text-ink-400 hover:text-red-600"><x-icon name="trash"/></button>
            </div>
        </template>
        <button type="button" @click="add()" class="add-row"><x-icon name="plus" class="size-[18px]"/>Add transport</button>
    </section>

    <section class="card space-y-4 p-6" x-data="repeater(@js($visas), { visa_type: 'Umrah visa', supplier: '', quantity: {{ max(1, $booking->passengers->count()) }} }, true)">
        <x-card-head icon="passport" title="Visa details" sub="Visa type, supplier and quantity"/>
        <div x-show="rows.length" class="hidden grid-cols-[1fr_1fr_120px_24px] gap-3 rounded-lg bg-line-soft px-3 py-2.5 md:grid">
            @foreach(['Visa type','Supplier','No. of visas',''] as $h)<span class="eyebrow">{{ $h }}</span>@endforeach
        </div>
        <template x-for="(r, i) in rows" :key="r._k">
            <div class="grid grid-cols-[1fr_90px_24px] items-center gap-3 md:grid-cols-[1fr_1fr_120px_24px] md:px-3">
                <input class="input input-sm" list="dl-visa-type" :name="`visas[${i}][visa_type]`" x-model="r.visa_type" required>
                <input class="input input-sm order-last col-span-3 md:order-none md:col-span-1" list="dl-visa" :name="`visas[${i}][supplier]`" x-model="r.supplier" placeholder="Supplier">
                <input class="input input-sm" type="number" min="1" :name="`visas[${i}][quantity]`" x-model="r.quantity">
                <button type="button" @click="remove(i)" class="text-ink-400 hover:text-red-600"><x-icon name="trash"/></button>
            </div>
        </template>
        <button type="button" @click="add()" class="add-row"><x-icon name="plus" class="size-[18px]"/>Add visa</button>
    </section>

    <section class="card space-y-4 p-6" x-data="repeater(@js($prices), { pax_type: 'Infant', quantity: 1, unit_price: '' })">
        <x-card-head icon="wallet" title="Customer pricing" sub="Selling price per passenger type · {{ $c->currency }}"/>
        <div class="hidden grid-cols-[1fr_100px_140px_140px_24px] gap-3 rounded-lg bg-line-soft px-3 py-2.5 md:grid">
            <span class="eyebrow">Type</span><span class="eyebrow">No.</span><span class="eyebrow text-right">Price</span><span class="eyebrow text-right">Total</span><span></span>
        </div>
        <template x-for="(r, i) in rows" :key="r._k">
            <div class="grid grid-cols-[1fr_70px_100px_24px] items-center gap-3 md:grid-cols-[1fr_100px_140px_140px_24px] md:px-3">
                <input class="input input-sm" list="dl-pax" :name="`prices[${i}][pax_type]`" x-model="r.pax_type" required>
                <input class="input input-sm" type="number" min="1" :name="`prices[${i}][quantity]`" x-model="r.quantity" required>
                <input class="input input-sm text-right" type="number" step="0.01" min="0" :name="`prices[${i}][unit_price]`" x-model="r.unit_price" placeholder="0.00" required>
                <div class="hidden h-[38px] items-center justify-end rounded-lg bg-line-soft px-2.5 text-sm font-semibold md:flex" x-text="money(line(r))"></div>
                <button type="button" @click="remove(i)" x-show="rows.length > 1" class="text-ink-400 hover:text-red-600"><x-icon name="trash"/></button>
            </div>
        </template>
        <datalist id="dl-pax"><option value="Adult"><option value="Child"><option value="Infant"><option value="Adult (sharing)"></datalist>
        <button type="button" @click="add()" class="add-row"><x-icon name="plus" class="size-[18px]"/>Add price row (e.g. Infant)</button>
        <div class="flex flex-wrap items-center gap-3 rounded-xl bg-nav px-5 py-[18px]">
            <div class="flex-1"><p class="eyebrow text-nav-text!">Total package</p><p class="text-[13px] text-nav-text" x-text="rows.reduce((s, r) => s + (Number(r.quantity) || 0), 0) + ' passengers'"></p></div>
            <p class="text-[32px] leading-10 font-bold text-white"><span x-text="'{{ $sym }}' + money(sum())"></span> <span class="text-lg text-nav-text">{{ $c->currency }}</span></p>
        </div>
    </section>

    <x-slot:after>
        @if($booking->total_sale > 0)
        <section class="card space-y-4 p-6">
            <x-card-head icon="file" title="Invoice" :sub="$booking->invoice_no ? $booking->invoice_no.($booking->invoice_sent_at ? ' · last sent '.$booking->invoice_sent_at->format('d M, H:i') : ' · not sent yet') : 'Generated when you first view or send it'">
                @if($editable)
                <form method="POST" action="{{ route('bookings.status', $booking) }}" class="flex gap-1 rounded-[10px] bg-line-soft p-1">
                    @csrf @method('PATCH')
                    @foreach(['accepted' => 'bg-green-600', 'waiting' => 'bg-amber-600', 'rejected' => 'bg-red-600'] as $s => $dot)
                        <button name="status" value="{{ $s }}" class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold {{ $booking->status === $s ? 'bg-white text-ink-900 shadow-sm' : 'text-ink-500 hover:text-ink-900' }}"><i class="size-[7px] rounded-full {{ $dot }}"></i>{{ ucfirst($s) }}</button>
                    @endforeach
                </form>
                @endif
            </x-card-head>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('bookings.invoice', $booking) }}" target="_blank" class="btn btn-secondary"><x-icon name="eye" class="size-[18px]"/>View invoice</a>
                @if($editable)
                <form method="POST" action="{{ route('bookings.invoice.send', $booking) }}">@csrf
                    <button class="btn btn-primary" @disabled(! $booking->email) title="{{ $booking->email ? 'Send to '.$booking->email : 'Add an email in step 1' }}"><x-icon name="send" class="size-[18px]"/>Send on email</button>
                </form>
                @endif
            </div>
            <p class="text-xs text-ink-500">Prices above are saved when you click <b>Save &amp; Next</b> or <b>Save draft</b> — save first if you changed them.</p>
        </section>
        @endif
    </x-slot:after>
</x-wizard>

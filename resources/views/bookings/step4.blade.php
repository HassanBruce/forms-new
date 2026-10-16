@php
    $c = $booking->company;
    $sym = rtrim($c->money(0), '0.');
    $costs = old('costs', $booking->costs->isNotEmpty()
        ? $booking->costs->map(fn ($x) => ['category' => $x->category, 'description' => $x->description, 'quantity' => $x->quantity, 'unit_cost' => (float) $x->unit_cost])->values()->all()
        : ($suggestedCosts ?? []));
    $sections = [
        'flight' => ['Flights', 'plane', 'Flight', 'No. of pax', 'Per pax cost'],
        'hotel' => ['Hotels', 'hotel', 'Hotel city', 'No. of nights', 'Per night rate'],
        'transport' => ['Transport', 'car', 'Vehicle type', 'No. of vehicles', 'Per vehicle rate'],
        'visa' => ['Visa', 'passport', 'Visa type', 'No. of visas', 'Per visa cost'],
        'other' => ['Other costs', 'briefcase', 'Item', 'Qty', 'Unit cost'],
    ];
@endphp
<x-wizard :booking="$booking" :step="4" :editable="$editable">
    <div class="flex items-center gap-2.5 rounded-[10px] bg-amber-50 px-3.5 py-2.5 text-[13px] text-amber-700">
        <x-icon name="lock" class="size-[18px] shrink-0"/>Internal only — visible to the agent, team lead, director and accounts. Never shown to the customer.
    </div>

    <div x-data="repeater(@js($costs), {}, true)" class="space-y-5">
        <section class="card grid divide-y divide-line p-6 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            @php $sale = (float) $booking->total_sale; @endphp
            <div class="pb-4 sm:pr-6 sm:pb-0"><p class="eyebrow">Total sale</p><p class="mt-1.5 text-[32px] leading-10 font-bold">{{ $c->money($sale) }}</p><p class="text-[13px] text-ink-500">From customer pricing</p></div>
            <div class="py-4 sm:px-6 sm:py-0"><p class="eyebrow">Total purchase</p><p class="mt-1.5 text-[32px] leading-10 font-bold" x-text="'{{ $sym }}' + money(sum('quantity','unit_cost'))"></p><p class="text-[13px] text-ink-500">Sum of supplier costs</p></div>
            <div class="pt-4 sm:pl-6 sm:pt-0"><p class="eyebrow">Margin</p>
                <p class="mt-1.5 text-[32px] leading-10 font-bold" :class="{{ $sale }} - sum('quantity','unit_cost') >= 0 ? 'text-green-600' : 'text-red-600'" x-text="'{{ $sym }}' + money({{ $sale }} - sum('quantity','unit_cost'))"></p>
                <p class="text-[13px] text-ink-500" x-text="({{ $sale }} > 0 ? (({{ $sale }} - sum('quantity','unit_cost')) / {{ $sale ?: 1 }} * 100).toFixed(1) : '0.0') + '% of sale'"></p></div>
        </section>

        @foreach($sections as $cat => [$title, $icon, $h1, $h2, $h3])
            <section class="card space-y-3 p-6">
                <x-card-head :icon="$icon" :title="$title" sub="Purchase cost">
                    <span class="text-[13px] text-ink-500">Subtotal <b class="ml-1 text-[15px] text-ink-900" x-text="money(sum('quantity','unit_cost','{{ $cat }}'))"></b></span>
                </x-card-head>
                <div class="hidden grid-cols-[1fr_130px_150px_130px_24px] gap-3 rounded-lg bg-line-soft px-3 py-2.5 md:grid" x-show="rows.some(r => r.category === '{{ $cat }}')">
                    <span class="eyebrow">{{ $h1 }}</span><span class="eyebrow">{{ $h2 }}</span><span class="eyebrow text-right">{{ $h3 }}</span><span class="eyebrow text-right">Total</span><span></span>
                </div>
                <template x-for="[r, i] in rows.map((r, i) => [r, i]).filter(x => x[0].category === '{{ $cat }}')" :key="r._k">
                    <div class="grid grid-cols-[1fr_70px_100px_24px] items-center gap-3 md:grid-cols-[1fr_130px_150px_130px_24px] md:px-3">
                        <input type="hidden" :name="`costs[${i}][category]`" value="{{ $cat }}">
                        <input class="input input-sm" :name="`costs[${i}][description]`" x-model="r.description" required>
                        <input class="input input-sm" type="number" min="0" :name="`costs[${i}][quantity]`" x-model="r.quantity" required>
                        <input class="input input-sm text-right" type="number" step="0.01" min="0" :name="`costs[${i}][unit_cost]`" x-model="r.unit_cost" placeholder="0.00" required>
                        <div class="hidden h-[38px] items-center justify-end rounded-lg bg-line-soft px-2.5 text-sm font-semibold md:flex" x-text="money(line(r,'quantity','unit_cost'))"></div>
                        <button type="button" @click="remove(i)" class="text-ink-400 hover:text-red-600"><x-icon name="trash"/></button>
                    </div>
                </template>
                <button type="button" @click="rows.push({ category: '{{ $cat }}', description: '{{ $cat === 'other' ? '' : rtrim($title, 's') }}', quantity: 1, unit_cost: '', _k: ++_seq })" class="add-row"><x-icon name="plus" class="size-[18px]"/>Add {{ strtolower(rtrim($title, 's')) }}</button>
            </section>
        @endforeach
    </div>
</x-wizard>

@php
    $hotels = old('hotels', $booking->hotels->map(fn ($h) => array_merge($h->only(['city','hotel_name','room_type','rooms','meal_type','view_type','supplier','ref_no','hcn']), ['check_in' => $h->check_in?->format('Y-m-d'), 'check_out' => $h->check_out?->format('Y-m-d')]))->values()->all());
    $blankHotel = ['city' => '', 'hotel_name' => '', 'room_type' => '', 'rooms' => 1, 'meal_type' => '', 'view_type' => '', 'check_in' => '', 'check_out' => '', 'supplier' => '', 'ref_no' => '', 'hcn' => ''];
@endphp
<x-wizard :booking="$booking" :step="2" :editable="$editable">
    @include('bookings._datalists')
    <section class="card space-y-4 p-6">
        <x-card-head icon="plane" title="Flight details" sub="Paste the GDS itinerary — it stays editable"/>
        <div class="grid gap-4 md:grid-cols-3">
            <div><label class="label">PNR</label><input name="pnr" value="{{ old('pnr', $booking->pnr) }}" class="input uppercase" placeholder="6KQ2LM"></div>
            <div><label class="label">Airline</label><input name="airline" value="{{ old('airline', $booking->airline) }}" class="input" placeholder="Saudia (SV)"></div>
            <div><label class="label">Flight supplier</label><input name="flight_supplier" list="dl-flight" value="{{ old('flight_supplier', $booking->flight_supplier) }}" class="input" placeholder="Sabre GDS"></div>
        </div>
        <div><label class="label">Itinerary details</label>
            <textarea name="flight_details" rows="6" class="input bg-canvas font-medium leading-5" placeholder="1  SV 125  Y  12NOV  LHRJED  HK5  2130  0640+1">{{ old('flight_details', $booking->flight_details) }}</textarea></div>
    </section>

    <div x-data="repeater(@js($hotels), @js($blankHotel))" class="space-y-5">
        <template x-for="(h, i) in rows" :key="h._k">
            <section class="card space-y-4 p-6">
                <div class="flex items-center gap-3">
                    <span class="icon-tile"><x-icon name="hotel" class="size-[18px]"/></span>
                    <div class="flex-1"><h3 class="text-[15px] font-semibold" x-text="'Hotel' + (h.city ? ' · ' + h.city : ' ' + (i + 1))"></h3>
                        <p class="text-[13px] text-ink-500" x-text="(h.hotel_name || 'Hotel name') + (h.check_in && h.check_out ? ' · ' + Math.max(0, Math.round((new Date(h.check_out) - new Date(h.check_in)) / 864e5)) + ' nights' : '')"></p></div>
                    <button type="button" @click="remove(i)" class="btn btn-ghost btn-sm"><x-icon name="trash" class="size-4"/>Remove</button>
                </div>
                <div class="grid gap-4 md:grid-cols-4">
                    <div><label class="label">City</label><input class="input" list="dl-city" :name="`hotels[${i}][city]`" x-model="h.city" placeholder="Makkah"></div>
                    <div><label class="label">Hotel name *</label><input class="input" :name="`hotels[${i}][hotel_name]`" x-model="h.hotel_name" required></div>
                    <div><label class="label">Room type</label><input class="input" list="dl-room" :name="`hotels[${i}][room_type]`" x-model="h.room_type" placeholder="Quad"></div>
                    <div><label class="label">No. of rooms</label><input type="number" min="1" class="input" :name="`hotels[${i}][rooms]`" x-model="h.rooms"></div>
                    <div><label class="label">Meal type</label><input class="input" list="dl-meal" :name="`hotels[${i}][meal_type]`" x-model="h.meal_type" placeholder="Breakfast"></div>
                    <div><label class="label">View type</label><input class="input" list="dl-view" :name="`hotels[${i}][view_type]`" x-model="h.view_type" placeholder="Haram view"></div>
                    <div><label class="label">Check-in</label><input type="date" class="input" :name="`hotels[${i}][check_in]`" x-model="h.check_in"></div>
                    <div><label class="label">Check-out</label><input type="date" class="input" :name="`hotels[${i}][check_out]`" x-model="h.check_out" :min="h.check_in"></div>
                    <div class="md:col-span-2"><label class="label">Supplier</label><input class="input" list="dl-hotel" :name="`hotels[${i}][supplier]`" x-model="h.supplier"></div>
                    <div><label class="label">Supplier ref no.</label><input class="input" :name="`hotels[${i}][ref_no]`" x-model="h.ref_no"></div>
                    <div><label class="label">HCN (hotel confirmation)</label><input class="input" :name="`hotels[${i}][hcn]`" x-model="h.hcn"></div>
                </div>
            </section>
        </template>
        <button type="button" @click="add()" class="add-row py-3.5"><x-icon name="plus" class="size-[18px]"/>Add <span x-text="rows.length ? 'another hotel' : 'a hotel'"></span></button>
    </div>
</x-wizard>

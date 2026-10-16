@php
    $pax = old('passengers', $booking->passengers->map->only(['salutation','first_name','surname','type'])->values()->all());
@endphp
<x-wizard :booking="$booking" :step="1" :editable="$editable">
    <section class="card space-y-5 p-6">
        <x-card-head icon="user" title="Lead guest & company" sub="Main contact for this booking"/>
        <div class="grid gap-4 md:grid-cols-3">
            <div><label class="label">Profile No.</label><input name="profile_no" value="{{ old('profile_no', $booking->profile_no) }}" class="input" placeholder="PRF-0001"></div>
            <div><label class="label">Company code</label>
                <select name="brand_id" class="input">@foreach($brands as $br)<option value="{{ $br->id }}" @selected(old('brand_id', $booking->brand_id) == $br->id)>{{ $br->code }} — {{ $br->name }}</option>@endforeach</select></div>
            <div><label class="label">Lead guest name *</label><input name="lead_name" value="{{ old('lead_name', $booking->lead_name) }}" required class="input" placeholder="Full name"></div>
            <div class="md:col-span-2"><label class="label">Email</label><input type="email" name="email" value="{{ old('email', $booking->email) }}" class="input" placeholder="Invoice is sent here"></div>
            <div><label class="label">Contact no.</label><input name="phone" value="{{ old('phone', $booking->phone) }}" class="input" placeholder="+44 …"></div>
        </div>
    </section>

    <section class="card space-y-4 p-6" x-data="repeater(@js($pax), { salutation: 'Mr.', first_name: '', surname: '', type: 'adult' })">
        <x-card-head icon="users" title="Passenger details" sub="Names exactly as on passport">
            <span class="rounded-full bg-line-soft px-2.5 py-1 text-xs font-medium text-ink-700"
                  x-text="['adult','child','infant'].map(t => [t, rows.filter(r => r.type === t).length]).filter(x => x[1]).map(x => x[1] + ' ' + (x[0] === 'child' ? (x[1] > 1 ? 'Children' : 'Child') : x[0][0].toUpperCase() + x[0].slice(1) + (x[1] > 1 ? 's' : ''))).join(' · ')"></span>
        </x-card-head>
        <div class="hidden grid-cols-[28px_110px_1fr_1fr_120px_24px] gap-3 rounded-lg bg-line-soft px-3 py-2.5 md:grid">
            @foreach(['#','Salutation','First name','Surname','Type',''] as $h)<span class="eyebrow">{{ $h }}</span>@endforeach
        </div>
        <template x-for="(r, i) in rows" :key="r._k">
            <div class="grid grid-cols-2 items-center gap-3 rounded-lg border border-line-soft p-3 md:grid-cols-[28px_110px_1fr_1fr_120px_24px] md:border-0 md:px-3 md:py-0">
                <span class="hidden text-sm font-semibold text-ink-400 md:block" x-text="String(i + 1).padStart(2, '0')"></span>
                <select class="input input-sm" :name="`passengers[${i}][salutation]`" x-model="r.salutation">
                    @foreach(['Mr.','Mrs.','Ms.','Miss','Mstr.','Dr.'] as $s)<option>{{ $s }}</option>@endforeach
                </select>
                <input class="input input-sm col-span-2 md:col-span-1" :name="`passengers[${i}][first_name]`" x-model="r.first_name" placeholder="First name" required>
                <input class="input input-sm col-span-2 md:col-span-1" :name="`passengers[${i}][surname]`" x-model="r.surname" placeholder="Surname" required>
                <select class="input input-sm" :name="`passengers[${i}][type]`" x-model="r.type"><option value="adult">Adult</option><option value="child">Child</option><option value="infant">Infant</option></select>
                <button type="button" @click="remove(i)" x-show="rows.length > 1" class="text-ink-400 hover:text-red-600" title="Remove"><x-icon name="trash"/></button>
            </div>
        </template>
        <button type="button" @click="add()" class="add-row"><x-icon name="plus" class="size-[18px]"/>Add passenger</button>
    </section>
</x-wizard>

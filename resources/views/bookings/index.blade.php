<x-layouts.app title="Bookings">
    @php $u = auth()->user(); @endphp
    <x-page-header title="Bookings" :sub="match($u->role){ 'agent' => 'You see only your own bookings', 'team_lead' => 'Your team’s bookings', 'director' => 'All bookings in '.($u->brand->name ?? 'your brand'), default => 'All bookings across '.$u->company->name }">
        @if($u->canCreateBookings())<form method="POST" action="{{ route('bookings.store') }}">@csrf<button class="btn btn-primary"><x-icon name="plus" class="size-[18px]"/>New booking</button></form>@endif
    </x-page-header>

    <div class="flex gap-1 overflow-x-auto border-b border-line">
        @foreach(['all' => 'All', 'waiting' => 'Waiting', 'accepted' => 'Accepted', 'rejected' => 'Rejected', 'draft' => 'Drafts', 'unpaid' => 'Unpaid'] as $k => $l)
            <a href="{{ request()->fullUrlWithQuery(['tab' => $k, 'page' => null]) }}" class="flex items-center gap-2 whitespace-nowrap px-3 pt-2.5 pb-3 text-sm font-semibold {{ $tab === $k ? 'border-b-2 border-brand-600 text-brand-600' : 'text-ink-500 hover:text-ink-900' }}">
                {{ $l }}<span class="rounded-full px-1.5 text-xs {{ $tab === $k ? 'bg-brand-50' : 'bg-line-soft' }}">{{ $counts[$k] }}</span></a>
        @endforeach
    </div>

    <form class="flex flex-wrap items-center gap-3">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="flex h-[42px] w-full items-center gap-2 rounded-[10px] border border-line bg-white px-3 sm:w-[360px]"><x-icon name="search" class="size-[18px] text-ink-400"/><input name="q" value="{{ request('q') }}" placeholder="Search by ref, guest, PNR or HCN" class="w-full text-sm outline-none"></div>
        @if($brands->count() > 1 && $u->hasRole('admin','accounts'))
            <select name="brand" onchange="this.form.submit()" class="input w-auto"><option value="">Brand: All</option>@foreach($brands as $b)<option value="{{ $b->id }}" @selected(request('brand') == $b->id)>{{ $b->name }}</option>@endforeach</select>
        @endif
        @unless($u->hasRole('agent'))
            <select name="agent" onchange="this.form.submit()" class="input w-auto"><option value="">Agent: All</option>@foreach($agents as $a)<option value="{{ $a->id }}" @selected(request('agent') == $a->id)>{{ $a->name }}</option>@endforeach</select>
        @endunless
        <input type="date" name="from" value="{{ request('from') }}" onchange="this.form.submit()" class="input w-auto" title="Travelling from">
        @if(request()->hasAny(['q','brand','agent','from']))<a href="{{ route('bookings.index', ['tab' => $tab]) }}" class="btn btn-ghost"><x-icon name="x" class="size-4"/>Clear</a>@endif
    </form>

    @include('bookings._table', ['bookings' => $bookings])
    <div>{{ $bookings->links() }}</div>
</x-layouts.app>

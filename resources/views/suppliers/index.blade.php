<x-layouts.app title="Suppliers">
    <x-page-header title="Suppliers" sub="Hotels, transport, visa and flight suppliers — suggested while filling bookings"/>
    <div class="grid items-start gap-5 xl:grid-cols-[1fr_360px]">
        <div class="grid gap-5 md:grid-cols-2">
            @foreach(\App\Models\Supplier::TYPES as $t => $label)
                <section class="card space-y-3 p-5">
                    <x-card-head :icon="['flight'=>'plane','hotel'=>'hotel','transport'=>'car','visa'=>'passport','other'=>'briefcase'][$t]" :title="$label" :sub="($suppliers[$t] ?? collect())->count().' suppliers'"/>
                    @forelse($suppliers[$t] ?? [] as $s)
                        <div class="flex items-center gap-3 rounded-lg border border-line-soft px-3 py-2">
                            <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ $s->name }}</p>@if($s->contact)<p class="truncate text-xs text-ink-500">{{ $s->contact }}</p>@endif</div>
                            @unless(auth()->user()->hasRole('agent'))<form method="POST" action="{{ route('suppliers.destroy', $s) }}" onsubmit="return confirm('Remove supplier?')">@csrf @method('DELETE')<button class="text-ink-400 hover:text-red-600"><x-icon name="trash" class="size-[18px]"/></button></form>@endunless
                        </div>
                    @empty <p class="text-[13px] text-ink-400">None yet.</p> @endforelse
                </section>
            @endforeach
        </div>
        @unless(auth()->user()->hasRole('agent'))
        <form method="POST" action="{{ route('suppliers.store') }}" class="card space-y-4 p-5">
            @csrf
            <h3 class="text-[15px] font-semibold">Add supplier</h3>
            <div><label class="label">Name</label><input name="name" required class="input"></div>
            <div><label class="label">Type</label><select name="type" class="input">@foreach(\App\Models\Supplier::TYPES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
            <div><label class="label">Contact (optional)</label><input name="contact" class="input" placeholder="Phone or email"></div>
            <button class="btn btn-primary w-full"><x-icon name="plus" class="size-[18px]"/>Add supplier</button>
        </form>
        @endunless
    </div>
</x-layouts.app>

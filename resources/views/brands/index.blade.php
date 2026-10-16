@php $u = auth()->user(); $c = $u->company; @endphp
<x-layouts.app title="Sub-brands">
<div x-data="{ open: null }" class="space-y-6">
    <x-page-header title="Sub-brands" sub="Each brand has its own director, team and invoice branding">
        @if($u->canManageCompany())<button @click="open = 'new'" class="btn btn-primary"><x-icon name="plus" class="size-[18px]"/>Add sub-brand</button>@endif
    </x-page-header>
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach($brands as $br)
            <div class="card space-y-4 p-5">
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-[10px] text-sm font-bold text-white" style="background: {{ $br->color }}">{{ $br->initials() }}</span>
                    <div class="flex-1"><p class="font-semibold">{{ $br->name }}</p><p class="text-xs text-ink-500">Code {{ $br->code }}{{ $br->email ? ' · '.$br->email : '' }}</p></div>
                    <button @click="open = {{ $br->id }}" class="btn btn-ghost btn-sm">Edit</button>
                </div>
                <div class="grid grid-cols-3 gap-2 rounded-xl bg-canvas p-3 text-center">
                    <div><p class="text-lg font-bold">{{ $br->users_count }}</p><p class="text-xs text-ink-500">Users</p></div>
                    <div><p class="text-lg font-bold">{{ $br->bookings_count }}</p><p class="text-xs text-ink-500">Bookings</p></div>
                    <div><p class="text-lg font-bold">{{ $c->money($br->bookings_sum_total_sale ?? 0) }}</p><p class="text-xs text-ink-500">Sales</p></div>
                </div>
            </div>
        @endforeach
    </div>

    @foreach(array_merge(['new'], $brands->pluck('id')->all()) as $key)
        @php $br = $key === 'new' ? new \App\Models\Brand(['color' => '#16A34A']) : $brands->firstWhere('id', $key); @endphp
        <div x-cloak x-show="open === @js($key)" class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-ink-900/50 p-4">
            <form method="POST" action="{{ $key === 'new' ? route('brands.store') : route('brands.update', $br) }}" class="card w-full max-w-2xl space-y-4 p-6" @click.outside="open = null">
                @csrf @if($key !== 'new') @method('PUT') @endif
                <h3 class="text-lg font-semibold">{{ $key === 'new' ? 'Add sub-brand' : 'Edit '.$br->name }}</h3>
                <div class="grid gap-4 sm:grid-cols-[1fr_120px_90px]">
                    <div><label class="label">Brand name</label><input name="name" value="{{ $br->name }}" required class="input"></div>
                    <div><label class="label">Code</label><input name="code" value="{{ $br->code }}" required maxlength="10" class="input uppercase"></div>
                    <div><label class="label">Colour</label><input type="color" name="color" value="{{ $br->color }}" class="input p-1"></div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="label">Email</label><input type="email" name="email" value="{{ $br->email }}" class="input"></div>
                    <div><label class="label">Phone</label><input name="phone" value="{{ $br->phone }}" class="input"></div>
                    <div><label class="label">Address</label><input name="address" value="{{ $br->address }}" class="input"></div>
                    <div><label class="label">Registration (ATOL / company no.)</label><input name="registration" value="{{ $br->registration }}" class="input"></div>
                    <div><label class="label">Bank details (shown on invoice)</label><textarea name="bank_details" rows="3" class="input">{{ $br->bank_details }}</textarea></div>
                    <div><label class="label">Invoice terms</label><textarea name="invoice_terms" rows="3" class="input">{{ $br->invoice_terms }}</textarea></div>
                </div>
                <div class="flex justify-end gap-2"><button type="button" @click="open = null" class="btn btn-secondary">Cancel</button><button class="btn btn-primary">Save</button></div>
            </form>
        </div>
    @endforeach
</div>
</x-layouts.app>

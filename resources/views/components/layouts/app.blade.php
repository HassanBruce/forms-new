@php
    $u = auth()->user();
    $company = $u->company;
    $nav = [
        'Main' => [
            ['Dashboard', 'dashboard', 'dashboard', 'dashboard'],
            ['Bookings', 'briefcase', 'bookings.index', 'bookings.*'],
            ['Invoices', 'file', 'invoices.index', 'invoices.*'],
        ],
        'Finance' => [
            ['Payments', 'wallet', 'payments.index', 'payments.*'],
            ['Profit & Loss', 'chart', 'reports.pl', 'reports.*'],
        ],
        'Admin' => array_values(array_filter([
            ['Team & roles', 'users', 'team.index', 'team.*'],
            $u->hasRole('admin', 'director') ? ['Sub-brands', 'building', 'brands.index', 'brands.*'] : null,
            ['Suppliers', 'hierarchy', 'suppliers.index', 'suppliers.*'],
            ['Settings', 'settings', 'settings.edit', 'settings.*'],
        ])),
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Tripdesk' }} · {{ $company->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ nav: false }" class="min-h-screen">
<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <div x-cloak x-show="nav" @click="nav=false" class="fixed inset-0 z-30 bg-ink-900/50 lg:hidden"></div>
    <aside :class="nav ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col gap-5 overflow-y-auto bg-nav px-4 pt-5 pb-4 transition lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 pl-1">
            <span class="grid size-[34px] place-items-center rounded-[10px] bg-gradient-to-br from-indigo-500 to-violet-600 text-white"><x-icon name="plane" class="size-[18px]"/></span>
            <span><span class="block text-[15px] font-semibold text-white">Tripdesk</span><span class="block text-xs text-nav-text">Travel agency OS</span></span>
        </a>
        <div class="flex items-center gap-2.5 rounded-[10px] border border-white/5 bg-nav-item p-2.5">
            <span class="grid size-7 place-items-center rounded-lg text-xs font-semibold text-white" style="background: {{ $u->hasRole('admin', 'accounts') ? '#4F46E5' : ($u->brand->color ?? '#4F46E5') }}">{{ $u->hasRole('admin', 'accounts') || ! $u->brand ? collect(explode(' ', $company->name))->take(2)->map(fn ($w) => strtoupper($w[0]))->join('') : $u->brand->initials() }}</span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-semibold text-white">{{ $u->hasRole('admin', 'accounts') ? $company->name : ($u->brand->name ?? $company->name) }}</span>
                <span class="block text-xs text-nav-text">{{ $u->hasRole('admin', 'accounts') ? 'Parent company' : 'Sub-brand' }} · {{ $u->roleLabel() }}</span>
            </span>
        </div>
        @foreach($nav as $section => $items)
            <nav class="space-y-0.5">
                <p class="eyebrow px-3 pb-1.5">{{ $section }}</p>
                @foreach($items as [$label, $icon, $route, $pattern])
                    <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}"><x-icon :name="$icon"/>{{ $label }}</a>
                @endforeach
                @if($section === 'Main' && $u->canCreateBookings())
                    <form method="POST" action="{{ route('bookings.store') }}">@csrf
                        <button class="nav-link w-full"><x-icon name="plus"/>New booking</button>
                    </form>
                @endif
            </nav>
        @endforeach
        <div class="mt-auto flex items-center gap-2.5 border-t border-white/10 px-2 pt-3">
            <x-avatar :text="$u->initials()" size="size-[34px]"/>
            <span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold text-white">{{ $u->name }}</span><span class="block truncate text-xs text-nav-text">{{ $u->roleLabel() }}{{ $u->team ? ' · '.$u->team->name : '' }}</span></span>
            <form method="POST" action="{{ route('logout') }}">@csrf<button title="Sign out" class="text-nav-text hover:text-white"><x-icon name="logout" class="size-[18px]"/></button></form>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 flex h-[68px] items-center gap-4 border-b border-line bg-white/90 px-4 backdrop-blur sm:px-8">
            <button @click="nav=true" class="btn-ghost -ml-2 rounded-lg p-2 lg:hidden"><x-icon name="menu"/></button>
            <form action="{{ route('bookings.index') }}" class="flex h-10 w-full max-w-[420px] items-center gap-2 rounded-[10px] border border-line bg-canvas px-3">
                <x-icon name="search" class="size-[18px] text-ink-400"/>
                <input name="q" value="{{ request('q') }}" placeholder="Search bookings, PNR, passengers…" class="w-full bg-transparent text-sm outline-none placeholder:text-ink-400">
            </form>
            <div class="flex-1"></div>
            <span class="hidden rounded-[10px] border border-line px-3 py-2 text-sm font-semibold text-ink-700 sm:inline">{{ $company->currency }}</span>
            @if($u->canCreateBookings())
                <form method="POST" action="{{ route('bookings.store') }}">@csrf<button class="btn btn-primary"><x-icon name="plus" class="size-[18px]"/><span class="hidden sm:inline">New booking</span></button></form>
            @endif
        </header>

        <main class="flex-1 space-y-6 px-4 pt-7 pb-10 sm:px-8">
            @if(session('ok'))
                <div x-data="{ s: true }" x-show="s" x-init="setTimeout(() => s = false, 5000)" class="flex items-center gap-3 rounded-[10px] border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    <x-icon name="check" class="size-[18px]"/>{{ session('ok') }}
                </div>
            @endif
            @if($errors->any())
                <div class="rounded-[10px] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 list-disc pl-5">@foreach(collect($errors->all())->unique()->take(6) as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>

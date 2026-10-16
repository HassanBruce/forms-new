@props(['title' => 'Sign in'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Tripdesk</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white">
<div class="grid min-h-screen lg:grid-cols-[640px_1fr]">
    <aside class="relative hidden flex-col gap-6 bg-[linear-gradient(150deg,#0B1020_0%,#29217A_60%,#4F46E5_100%)] px-14 py-12 lg:flex">
        <div class="flex items-center gap-2.5"><span class="grid size-[38px] place-items-center rounded-[10px] bg-white/10 text-white"><x-icon name="plane"/></span><span class="text-lg font-semibold text-white">Tripdesk</span></div>
        <div class="mt-auto space-y-5">
            <h2 class="text-[38px] leading-[48px] font-bold tracking-[-.5px] text-white">Run every booking, invoice and payment of your travel agency — in one place.</h2>
            <p class="text-base leading-[26px] text-nav-text">Umrah & tour packages, multi-brand teams, live profit per booking and installment tracking — built for travel agencies.</p>
            <ul class="space-y-3.5 pt-2">
                @foreach([['users','Parent company → sub-brands → team leads → agents'],['chart','Profit / loss on every booking, automatically'],['wallet','Installments with payment proof & pending balance']] as [$i,$t])
                    <li class="flex items-center gap-3 text-sm font-semibold text-white"><span class="grid size-9 place-items-center rounded-[10px] bg-white/10"><x-icon :name="$i" class="size-[18px]"/></span>{{ $t }}</li>
                @endforeach
            </ul>
        </div>
        <div class="mt-auto rounded-2xl border border-white/10 bg-white/[.08] p-5 text-[15px] leading-6 text-white">“We closed our Excel sheets for good. Every agent knows their margin before they quote.”<p class="mt-3 text-xs text-nav-text">Agency director</p></div>
    </aside>
    <main class="grid place-items-center px-6 py-12">{{ $slot }}</main>
</div>
</body>
</html>

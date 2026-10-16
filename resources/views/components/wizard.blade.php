@props(['booking', 'step', 'editable' => true, 'formless' => false])
@php $c = $booking->company; $b = $booking; @endphp
<x-layouts.app :title="$b->ref.' · '.\App\Models\Booking::STEPS[$step]">
    <x-page-header :title="'Booking '.$b->ref">
        <x-slot:crumbs><p class="mb-1.5 text-[13px] text-ink-500"><a href="{{ route('bookings.index') }}" class="hover:text-ink-900">Bookings</a> <span class="text-ink-400">/</span> <span class="text-ink-700">{{ $b->ref }}</span></p></x-slot:crumbs>
        <div class="flex items-center gap-3">
            <x-badge :status="$b->status"/>
            @if($b->total_sale > 0)<a href="{{ route('bookings.invoice', $b) }}" target="_blank" class="btn btn-secondary"><x-icon name="eye" class="size-[18px]"/>Invoice</a>@endif
            @if($editable && ! $formless)<button form="step-form" name="_action" value="stay" class="btn btn-secondary">Save draft</button>@endif
        </div>
    </x-page-header>

    {{-- Stepper --}}
    <nav class="card flex items-center gap-3 overflow-x-auto p-4">
        @foreach(\App\Models\Booking::STEPS as $n => $label)
            @php $now = $n === $step; $done = ! $now && $n < $b->step; $reachable = $n <= $b->step; @endphp
            <a @if($reachable) href="{{ route('bookings.step', [$b, $n]) }}" @endif class="flex shrink-0 items-center gap-2.5 {{ $reachable ? '' : 'pointer-events-none' }}">
                <span class="grid size-[30px] place-items-center rounded-full text-xs font-semibold {{ $now ? 'bg-brand-600 text-white' : ($done ? 'bg-green-600 text-white' : 'border border-line text-ink-500') }}">
                    @if($done)<x-icon name="check" class="size-4"/>@else{{ $n }}@endif
                </span>
                <span><span class="block text-[11px] font-semibold tracking-[.6px] {{ $now ? 'text-brand-600' : 'text-ink-400' }}">STEP {{ $n }}</span><span class="block text-sm font-semibold {{ $now || $done ? 'text-ink-900' : 'text-ink-500' }}">{{ $label }}</span></span>
            </a>
            @if($n < 5)<span class="h-0.5 min-w-5 flex-1 rounded {{ $n < $b->step ? 'bg-green-600' : 'bg-line' }}"></span>@endif
        @endforeach
    </nav>

    <div class="grid items-start gap-6 xl:grid-cols-[1fr_320px]">
        <div class="min-w-0 space-y-5">
            @if($formless)
                {{ $slot }}
            @else
                <form id="step-form" method="POST" action="{{ route('bookings.step.save', [$b, $step]) }}" class="space-y-5">
                    @csrf @method('PUT')
                    <fieldset @disabled(! $editable) class="space-y-5">{{ $slot }}</fieldset>
                </form>
            @endif
            {{ $after ?? '' }}
        </div>
        <aside class="card space-y-4 p-6 xl:sticky xl:top-24">
            <h3 class="text-[15px] font-semibold">Booking summary</h3>
            @foreach(['Lead guest' => $b->lead_name ?: '—', 'Passengers' => $b->paxSummary(), 'Travel' => $b->travelRange(), 'Brand' => $b->brand->code.' · '.$b->brand->name, 'Agent' => $b->agent->name ?? '—'] as $k => $v)
                <div class="flex items-start justify-between gap-3 text-sm"><span class="text-[13px] text-ink-500">{{ $k }}</span><span class="text-right font-semibold">{{ $v }}</span></div>
            @endforeach
            <hr class="border-line">
            <div class="rounded-xl bg-brand-50 px-4 py-3.5">
                <p class="eyebrow text-brand-600!">Total package</p>
                <p class="text-[32px] leading-10 font-bold">{{ $b->total_sale > 0 ? $c->money($b->total_sale) : '—' }}</p>
                <p class="text-[13px] text-ink-500">{{ $b->total_sale > 0 ? 'Paid '.$c->money($b->total_paid).' · Pending '.$c->money($b->pending()) : 'Set prices in step 3' }}</p>
            </div>
        </aside>
    </div>

    <div class="flex items-center gap-3">
        @if($step > 1)<a href="{{ route('bookings.step', [$b, $step - 1]) }}" class="btn btn-secondary"><x-icon name="arrow-left" class="size-[18px]"/>Back</a>@endif
        <div class="flex-1"></div>
        <span class="text-[13px] text-ink-500">Step {{ $step }} of 5</span>
        @if($step < 5)
            @if($editable)
                <button form="step-form" class="btn btn-primary"><x-icon name="arrow-right" class="size-[18px]"/>Save &amp; Next</button>
            @else
                <a href="{{ route('bookings.step', [$b, $step + 1]) }}" class="btn btn-primary"><x-icon name="arrow-right" class="size-[18px]"/>Next</a>
            @endif
        @else
            <a href="{{ route('bookings.index') }}" class="btn btn-primary"><x-icon name="check" class="size-[18px]"/>Finish booking</a>
        @endif
    </div>
</x-layouts.app>

@props(['icon', 'title', 'sub' => null, 'tone' => 'brand'])
@php $tones = ['brand' => 'bg-brand-50 text-brand-600', 'success' => 'bg-green-50 text-green-600', 'warning' => 'bg-amber-50 text-amber-600', 'info' => 'bg-sky-50 text-sky-600']; @endphp
<div class="flex flex-wrap items-center gap-3">
    <div class="grid size-9 shrink-0 place-items-center rounded-[10px] {{ $tones[$tone] }}"><x-icon :name="$icon" class="size-[18px]"/></div>
    <div class="min-w-0 flex-1">
        <h3 class="text-[15px] font-semibold leading-[22px]">{{ $title }}</h3>
        @if($sub)<p class="text-[13px] text-ink-500">{{ $sub }}</p>@endif
    </div>
    {{ $slot }}
</div>

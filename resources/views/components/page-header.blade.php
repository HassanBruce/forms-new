@props(['title', 'sub' => null])
<div class="flex flex-wrap items-center gap-3">
    <div class="min-w-0 flex-1">
        {{ $crumbs ?? '' }}
        <h1 class="text-2xl font-bold tracking-[-.3px]">{{ $title }}</h1>
        @if($sub)<p class="mt-1 text-sm text-ink-500">{{ $sub }}</p>@endif
    </div>
    {{ $slot }}
</div>

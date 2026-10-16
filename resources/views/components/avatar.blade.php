@props(['text', 'tone' => 'brand', 'size' => 'size-8'])
@php $tones = ['brand' => 'bg-brand-50 text-brand-600', 'success' => 'bg-green-50 text-green-600', 'warning' => 'bg-amber-50 text-amber-600', 'info' => 'bg-sky-50 text-sky-600']; @endphp
<span {{ $attributes->merge(['class' => "grid $size shrink-0 place-items-center rounded-full text-xs font-semibold ".($tones[$tone] ?? $tones['brand'])]) }}>{{ $text }}</span>

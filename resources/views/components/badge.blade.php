@props(['status', 'label' => null])
<span {{ $attributes->merge(['class' => 'badge badge-'.$status]) }}>{{ $label ?? \Illuminate\Support\Str::headline($status) }}</span>

@props(['status' => null])
<span {{ $attributes->merge(['class' => 'badge '.\App\Support\Labels::badge($status)]) }}>
    {{ \App\Support\Labels::status($status) }}
</span>

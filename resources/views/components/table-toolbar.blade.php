@props([
    'placeholder' => 'Cari data',
    'filters' => [],
])
@php($searchId = 'cari-'.substr(md5($placeholder.json_encode($filters)), 0, 8))
<form class="table-search" method="GET" action="{{ url()->current() }}" role="search">
    @foreach (request()->except(array_merge(['q', 'page', 'menunggu', 'akun', 'kegiatan', 'produk', 'riwayat', 'pengeluaran'], array_column($filters, 'name'))) as $key => $value)
        @if (is_scalar($value))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <label class="sr-only" for="{{ $searchId }}">{{ $placeholder }}</label>
    <input id="{{ $searchId }}" name="q" value="{{ request('q') }}" placeholder="{{ $placeholder }}" enterkeyhint="search">
    @foreach ($filters as $filter)
        <select name="{{ $filter['name'] }}" aria-label="{{ $filter['label'] }}" onchange="this.form.requestSubmit()">
            <option value="">{{ $filter['label'] }}</option>
            @foreach ($filter['options'] as $value => $label)
                <option value="{{ $value }}" @selected((string) request($filter['name']) === (string) $value)>{{ $label }}</option>
            @endforeach
        </select>
    @endforeach
    <button class="icon-button search-submit" type="submit" aria-label="Cari"><x-icon name="search" /></button>
</form>

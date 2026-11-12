@props(['navigation'])
<nav class="mobile-bottom" aria-label="Navigasi utama">
    @foreach ($navigation['bottom'] as $item)
        @if (! empty($item['drawer']))
            <button class="mobile-tab" type="button" data-drawer-open><x-icon :name="$item['icon']" /><span>{{ $item['label'] }}</span></button>
        @else
            <a class="mobile-tab {{ request()->routeIs($item['route']) ? 'active' : '' }}" href="{{ route($item['route']) }}"><x-icon :name="$item['icon']" /><span>{{ $item['label'] }}</span></a>
        @endif
    @endforeach
</nav>

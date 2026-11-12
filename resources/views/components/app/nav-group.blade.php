@props(['group'])
@php
    $active = collect($group['items'])->contains(fn ($item) => request()->routeIs($item['route']));
@endphp
@if (! empty($group['standalone']))
    <div class="nav-group nav-group-standalone">
        @foreach ($group['items'] as $item)
            <a class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}" href="{{ route($item['route']) }}" @if(request()->routeIs($item['route'])) aria-current="page" @endif>
                <x-icon :name="$item['icon']" /><span>{{ $item['label'] }}</span>
                @if (! empty($item['count']))<span class="nav-count">{{ $item['count'] }}</span>@endif
            </a>
        @endforeach
    </div>
@else
    <details class="nav-group nav-group-collapsible" @if($active) open @endif>
        <summary class="nav-group-toggle">
            <span class="nav-group-symbol"><x-icon :name="$group['icon'] ?? 'dashboard'" /></span>
            <span class="nav-group-title">{{ $group['title'] }}</span>
            <span class="nav-group-count">{{ count($group['items']) }}</span>
        </summary>
        <div class="nav-group-items">
            @foreach ($group['items'] as $item)
                <a class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}" href="{{ route($item['route']) }}" @if(request()->routeIs($item['route'])) aria-current="page" @endif>
                    <x-icon :name="$item['icon']" /><span>{{ $item['label'] }}</span>
                    @if (! empty($item['count']))<span class="nav-count">{{ $item['count'] }}</span>@endif
                </a>
            @endforeach
        </div>
    </details>
@endif

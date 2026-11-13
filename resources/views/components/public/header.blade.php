@php
    $links = [
        ['route' => 'public.home', 'label' => 'Beranda'],
        ['route' => 'public.profile', 'label' => 'Profil RW'],
        ['route' => 'public.guide', 'label' => 'Panduan'],
        ['route' => 'public.events', 'label' => 'Kegiatan'],
        ['route' => 'public.catalog', 'label' => 'Produk warga'],
        ['route' => 'public.donation', 'label' => 'Donasi'],
    ];
@endphp
<header class="public-header">
    <div class="public-header-inner">
        <a class="brand public-brand" href="{{ route('public.home') }}" aria-label="SIRKULA-CERDAS beranda">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SIRKULA-CERDAS">
            <span class="brand-copy"><span class="brand-name">SIRKULA</span><span class="brand-sub">CERDAS · RW 13</span></span>
        </a>
        <nav class="public-nav" aria-label="Navigasi publik">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}" class="public-nav-link {{ request()->routeIs($link['route']) ? 'active' : '' }}" @if(request()->routeIs($link['route'])) aria-current="page" @endif>{{ $link['label'] }}</a>
            @endforeach
        </nav>
        <div class="public-actions">
            @auth
                <a class="btn outline" href="{{ route('app.home') }}">Ke aplikasi</a>
            @else
                <a class="btn outline" href="{{ route('login') }}">Masuk</a>
                <a class="btn primary" href="{{ route('register') }}">Daftar warga</a>
            @endauth
        </div>
    </div>
</header>

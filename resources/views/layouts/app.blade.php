<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#6F8F3A">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') — SIRKULA RW13</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800">
    <link rel="stylesheet" href="{{ asset('css/sirkula.css') }}?v={{ filemtime(public_path('css/sirkula.css')) }}">
</head>
<body>
    <x-splash />
    @php($navigation = \App\Support\Navigation::for(auth()->user()))
    <div id="app">
        <x-app.header />
        <div class="shell">
            <x-app.sidebar :navigation="$navigation" />
            <main class="main-column" id="main-content" tabindex="-1">
                <div class="page-header {{ request()->routeIs('app.home') ? 'home-page-header' : '' }}">
                    <div>
                        <div class="eyebrow">@yield('eyebrow', 'SIRKULA-CERDAS')</div>
                        <h1 class="page-title">@yield('heading', 'Beranda')</h1>
                        <p class="page-subtitle">@yield('subtitle')</p>
                    </div>
                    <div class="page-actions">@yield('actions')</div>
                </div>
                <x-flash />
                @if (auth()->user()->hasRole('warga') && auth()->user()->residentProfile && auth()->user()->residentProfile->status !== 'active')
                    <div class="callout warning account-pending">
                        <x-icon name="alert" />
                        <div>
                            <strong>Akun belum disetujui pengurus</strong>
                            Halaman warga bisa dibuka, tetapi ID warga belum terbit. Menyetor, menukar poin, mendaftar kegiatan, dan memesan produk baru bisa setelah akun disetujui.
                        </div>
                    </div>
                @endif
                @yield('content')
                <div class="footer-note">SIRKULA-CERDAS · RW 13</div>
            </main>
        </div>
        <x-app.bottom-nav :navigation="$navigation" />
        <x-confirm-dialog />
        @if ($errors->any())
            <script type="application/json" id="form-errors">@json($errors->getMessages())</script>
        @endif
        <script src="{{ asset('js/sirkula-ui.js') }}?v={{ filemtime(public_path('js/sirkula-ui.js')) }}"></script>
        <script src="{{ asset('js/sirkula-notices.js') }}?v={{ filemtime(public_path('js/sirkula-notices.js')) }}"></script>
        <x-splash script />
    </div>
    <script>
        const sidebar = document.getElementById('sidebar');
        const openDrawer = () => sidebar?.classList.add('open');
        const closeDrawer = () => sidebar?.classList.remove('open');
        document.querySelectorAll('[data-drawer-open]').forEach((button) => button.addEventListener('click', openDrawer));
        document.querySelectorAll('[data-drawer-close]').forEach((button) => button.addEventListener('click', closeDrawer));
        sidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeDrawer));
    </script>
</body>
</html>

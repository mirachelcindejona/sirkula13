<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#6F8F3A">
    <title>@yield('title', 'Beranda') — SIRKULA RW13</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800">
    <link rel="stylesheet" href="{{ asset('css/sirkula.css') }}?v={{ filemtime(public_path('css/sirkula.css')) }}">
</head>
<body>
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
                @yield('content')
                <div class="footer-note">SIRKULA-CERDAS · RW 13</div>
            </main>
        </div>
        <x-app.bottom-nav :navigation="$navigation" />
        <x-confirm-dialog />
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

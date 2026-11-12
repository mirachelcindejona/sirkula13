<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#6F8F3A">
    <title>@yield('title', 'SIRKULA RW13 · RW 13')</title>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800">
    <link rel="stylesheet" href="{{ asset('css/sirkula.css') }}?v={{ filemtime(public_path('css/sirkula.css')) }}">
</head>
<body>
    <div id="app">
        <x-public.header />
        <main class="public-main" id="main-content" tabindex="-1">
            <x-flash />
            @yield('content')
        </main>
        <x-public.footer />
        <x-confirm-dialog />
    </div>
</body>
</html>

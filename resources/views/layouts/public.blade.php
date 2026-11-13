<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#6F8F3A">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIRKULA RW13 · RW 13')</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800">
    <link rel="stylesheet" href="{{ asset('css/sirkula.css') }}?v={{ filemtime(public_path('css/sirkula.css')) }}">
</head>
<body>
    <x-splash />
    <div id="app">
        <x-public.header />
        <main class="public-main" id="main-content" tabindex="-1">
            <x-flash />
            @yield('content')
        </main>
        <x-public.footer />
        <x-confirm-dialog />
        @if ($errors->any())
            <script type="application/json" id="form-errors">@json($errors->getMessages())</script>
        @endif
        <script src="{{ asset('js/sirkula-ui.js') }}?v={{ filemtime(public_path('js/sirkula-ui.js')) }}"></script>
        <script src="{{ asset('js/sirkula-notices.js') }}?v={{ filemtime(public_path('js/sirkula-notices.js')) }}"></script>
        <x-splash script />
    </div>
</body>
</html>

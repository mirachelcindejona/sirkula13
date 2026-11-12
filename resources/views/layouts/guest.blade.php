<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') — SIRKULA RW 13</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-4 py-10">
        <div class="w-full">
            <a href="{{ route('public.home') }}" class="mb-6 flex items-center gap-2.5 no-underline">
                <img src="{{ asset('images/logo.png') }}" alt="" class="h-12 w-12 rounded-2xl object-cover">
                <span class="font-display text-2xl font-bold text-[#26331a]">SIRKULA RW 13</span>
            </a>
            <x-flash />
            @yield('content')
        </div>
    </main>
</body>
</html>

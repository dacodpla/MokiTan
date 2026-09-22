@props(['title' => 'Admin'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — Moki-Tan Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col bg-canvas text-ink">
    <header class="border-b border-ink/10">
        <div class="container mx-auto py-4 flex items-center justify-between gap-6">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('img/logo/MokiTan.svg') }}" alt="Moki-Tan" class="h-10 w-auto">
                <span class="type-h5 text-ink/60">/ Admin</span>
            </a>
            <nav class="flex items-center gap-4 type-body">
                <a href="{{ route('admin.products.index') }}" class="hover:text-brand
                   {{ request()->routeIs('admin.products.*') ? 'text-brand font-bold' : '' }}">Products</a>
                <a href="{{ route('home') }}" class="hover:text-brand text-ink/60">← Store</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-brand text-ink/60">Logout</button>
                </form>
            </nav>
        </div>
    </header>

    @if (session('status'))
        <div class="bg-brand text-canvas">
            <div class="container mx-auto py-3 type-body">{{ session('status') }}</div>
        </div>
    @endif

    <main class="flex-1 container mx-auto py-10">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>

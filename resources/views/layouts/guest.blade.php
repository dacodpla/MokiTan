<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Account — Moki-Tan' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-canvas text-ink">
    <x-store.header />

    <main class="flex-1 flex items-center justify-center py-16 px-4">
        <div class="w-full max-w-md">
            <div class="rounded-xl border border-ink/10 bg-canvas p-8">
                {{ $slot }}
            </div>
        </div>
    </main>

    <x-store.footer />
</body>
</html>

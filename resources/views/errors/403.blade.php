<x-layouts.store>
    <x-slot name="title">403 — Akses ditolak</x-slot>

    <div class="container mx-auto py-24 max-w-xl text-center">
        <div class="type-display text-brand">403</div>
        <h1 class="type-h2 mt-2">Akses ditolak</h1>
        <p class="type-body text-ink/60 mt-3">
            {{ $exception->getMessage() ?: 'Kamu tidak punya izin untuk membuka halaman ini.' }}
        </p>
        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('home') }}" class="btn-primary">Kembali ke beranda</a>
            @auth
                <a href="{{ route('orders.index') }}" class="btn-outline">Pesanan Saya</a>
            @endauth
        </div>
    </div>
</x-layouts.store>

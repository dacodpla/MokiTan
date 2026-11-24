<x-layouts.store>
    <x-slot name="title">404 — Halaman tidak ditemukan</x-slot>

    <div class="container mx-auto py-24 max-w-xl text-center">
        <div class="type-display text-brand">404</div>
        <h1 class="type-h2 mt-2">Halaman tidak ditemukan</h1>
        <p class="type-body text-ink/60 mt-3">
            Produk atau halaman yang kamu cari tidak ada, atau sudah dihapus.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('catalog') }}" class="btn-primary">Lihat katalog</a>
            <a href="{{ route('home') }}" class="btn-outline">Kembali ke beranda</a>
        </div>
    </div>
</x-layouts.store>

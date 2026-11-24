@php
    $navCategories = \App\Models\Category::orderBy('sort_order')->get(['name', 'slug']);
    $activeSlug = request()->segment(2);
@endphp

<header
    x-data="{
        show: true,
        lastY: 0,
        onScroll() {
            const y = window.scrollY;
            const delta = y - this.lastY;
            if (y < 40) {
                this.show = true;
            } else if (delta > 8) {
                this.show = false;
            } else if (delta < -8) {
                this.show = true;
            }
            this.lastY = y;
        },
    }"
    x-init="lastY = window.scrollY"
    @scroll.window.passive="onScroll()"
    :class="show ? 'translate-y-0' : '-translate-y-full'"
    class="sticky top-0 z-40 transition-transform duration-300 ease-out will-change-transform">
    {{-- Role-aware utility strip: guest / customer / admin --}}
    <div class="container mx-auto pt-3 flex flex-wrap items-center justify-between gap-x-6 gap-y-2 type-body text-sm">
        <div class="flex items-center gap-5">
            <a href="{{ route('catalog') }}" class="font-bold hover:text-brand transition">Katalog</a>
            <form action="{{ route('catalog') }}" method="GET" role="search" class="w-44 sm:w-64">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search kits, paints, brands..."
                       aria-label="Search products" class="input-underline py-1 text-sm">
            </form>
        </div>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            @guest
                <span>
                    <a href="{{ route('login') }}" class="font-bold hover:text-brand transition">Masuk</a>
                    <span class="text-ink/30">/</span>
                    <a href="{{ route('register') }}" class="font-bold hover:text-brand transition">Daftar</a>
                </span>
            @endguest

            @auth
                @if (auth()->user()->isAdmin())
                    <span>Seller: <strong>{{ auth()->user()->name }}</strong></span>
                    <a href="{{ route('admin.dashboard') }}" class="btn-primary btn-sm">Panel Seller Center</a>
                @else
                    <span>Halo, <strong>{{ auth()->user()->name }}</strong></span>
                    <a href="{{ route('orders.index') }}" class="font-bold hover:text-brand transition">Pesanan Saya</a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="font-bold text-ink/60 hover:text-brand transition">Keluar</button>
                </form>
            @endauth
        </div>
    </div>

    <div class="container mx-auto pt-5 pb-6">
        {{-- Logo, centered --}}
        <div class="flex justify-center">
            <a href="{{ route('home') }}" class="inline-block">
                <img src="{{ asset('img/logo/MokiTan.svg') }}" alt="Moki-Tan" class="h-16 w-auto">
            </a>
        </div>

        {{-- Pill nav + action icons --}}
        <div class="mt-6 flex items-center justify-center gap-3 flex-wrap">
            @foreach ($navCategories as $cat)
                <a href="{{ route('catalog.category', $cat->slug) }}"
                   class="shape-card bg-brand text-canvas px-10 py-4 type-h5 hover:bg-brand-hover transition
                          {{ $activeSlug === $cat->slug ? 'ring-4 ring-brand/30' : '' }}">
                    {{ $cat->name }}
                </a>
            @endforeach

            <div class="ml-4 flex items-center gap-3">
                <livewire:header.cart-icon />
                <a href="{{ Auth::check() ? route('profile.edit') : route('login') }}"
                   class="inline-flex h-11 w-11 items-center justify-center rounded-full border-2 border-ink/40 hover:border-brand hover:text-brand transition"
                   aria-label="Account">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</header>

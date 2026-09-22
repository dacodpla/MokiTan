@php
    $navCategories = \App\Models\Category::orderBy('sort_order')->get(['name', 'slug']);
    $activeSlug = request()->segment(2);
@endphp

<header class="bg-canvas">
    <div class="container mx-auto pt-8 pb-6">
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
                @auth
                    <a href="{{ route('admin.dashboard') }}"
                       class="type-body font-bold text-ink/70 hover:text-brand transition"
                       title="Admin panel">Admin</a>
                @endauth
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

<x-layouts.store>
    <x-slot name="title">Moki-Tan — Model Kits, Paints & Tools</x-slot>

    {{-- FEATURED --}}
    @if ($featured)
        <section class="container mx-auto pt-12">
            <div class="grid gap-10 items-center lg:grid-cols-2">
                <div class="aspect-[4/3] bg-surface overflow-hidden shape-card">
                    <img src="{{ asset($featured->image_path) }}" alt="{{ $featured->name }}"
                         class="h-full w-full object-cover">
                </div>
                <div class="flex flex-col">
                    <div class="section-heading w-full max-w-md">Featured</div>
                    <h1 class="type-h1 mt-6">{{ $featured->name }}</h1>
                    <p class="type-body mt-4 text-ink-alt/80 max-w-lg">
                        {{ \Illuminate\Support\Str::limit($featured->description, 340) }}
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('products.show', $featured->slug) }}" class="btn-primary inline-flex">
                            Check it out
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M7 17L17 7M9 7h8v8"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- NEW IN STOCK --}}
    <section class="container mx-auto pt-20">
        <div class="section-heading w-full max-w-md">New in Stock</div>

        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($newInStock as $product)
                <x-store.product-card :product="$product" />
            @endforeach
        </div>
    </section>

    {{-- SHOW ME MORE! --}}
    <section class="container mx-auto pt-12">
        <a href="{{ route('catalog') }}"
           class="block w-full shape-card bg-brand text-canvas text-center py-12 type-h1 hover:bg-brand-hover transition">
            Show me more!
        </a>
    </section>

    {{-- CATEGORY MOSAIC --}}
    @php
        $catBySlug  = $categories->keyBy('slug');
        $tileKits   = $catBySlug->get('kits');
        $tileTools  = $catBySlug->get('tools');
        $tilePaints = $catBySlug->get('paints');
        $tileMats   = $catBySlug->get('materials');
        $tileOther  = $catBySlug->get('other');
    @endphp

    <section class="container mx-auto pt-16 pb-4 space-y-6">
        {{-- Row 1: Kits (2/3) + Tools (1/3) --}}
        <div class="grid gap-6 md:grid-cols-3">
            @if ($tileKits)  <x-store.category-tile :category="$tileKits"  class="md:col-span-2" /> @endif
            @if ($tileTools) <x-store.category-tile :category="$tileTools" /> @endif
        </div>

        {{-- Row 2: Paints (1/2) + Materials (1/2) --}}
        <div class="grid gap-6 md:grid-cols-2">
            @if ($tilePaints) <x-store.category-tile :category="$tilePaints" /> @endif
            @if ($tileMats)   <x-store.category-tile :category="$tileMats"   /> @endif
        </div>

        {{-- Row 3: Other (full width) --}}
        @if ($tileOther)
            <x-store.category-tile :category="$tileOther" />
        @endif
    </section>
</x-layouts.store>

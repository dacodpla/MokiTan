@props(['product'])

<a href="{{ route('products.show', $product->slug) }}"
   class="group block overflow-hidden shape-card">
    {{-- Image on top (surface gray) --}}
    <div class="aspect-[4/3] bg-surface overflow-hidden">
        <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}"
             class="h-full w-full object-cover transition group-hover:scale-[1.03]">
    </div>

    {{-- Solid red band below --}}
    <div class="bg-brand text-canvas p-4">
        <div class="flex items-start justify-between gap-2">
            <div class="type-h5 leading-tight">{{ $product->name }}</div>
            <span class="shrink-0 transition group-hover:translate-x-1 group-hover:-translate-y-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M7 17L17 7M9 7h8v8"/>
                </svg>
            </span>
        </div>
        <div class="mt-6 flex items-end justify-between">
            <div class="type-body font-medium uppercase tracking-wide text-canvas/85">{{ $product->brand }}</div>
            <div class="type-body font-bold">{{ number_format((int) $product->price, 0, ',', '.') }}</div>
        </div>
    </div>
</a>

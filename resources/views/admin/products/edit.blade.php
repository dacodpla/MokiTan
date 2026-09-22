<x-layouts.admin title="Edit product">
    <a href="{{ route('admin.products.index') }}" class="type-body text-brand hover:underline">← All products</a>
    <div class="flex items-baseline justify-between mt-2 mb-6">
        <h1 class="type-h1">Edit: {{ $product->name }}</h1>
        <a href="{{ route('products.show', $product->slug) }}" target="_blank"
           class="type-body text-brand hover:underline">View on store ↗</a>
    </div>

    @include('admin.products._form', ['product' => $product, 'categories' => $categories])

    <section class="mt-10">
        <h2 class="type-h3 mb-4">Recommended add-ons</h2>
        <p class="type-body text-ink/60 mb-4">
            Products checked here appear in the <strong>Quick Add</strong> bundle modal on this product's page.
        </p>
        <livewire:admin.recommendations :product="$product" />
    </section>
</x-layouts.admin>

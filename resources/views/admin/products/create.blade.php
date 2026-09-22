<x-layouts.admin title="New product">
    <a href="{{ route('admin.products.index') }}" class="type-body text-brand hover:underline">← All products</a>
    <h1 class="type-h1 mt-2 mb-6">New product</h1>

    @include('admin.products._form', ['product' => null, 'categories' => $categories])
</x-layouts.admin>

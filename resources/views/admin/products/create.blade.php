<x-layouts.admin title="New product">
    <x-store.breadcrumb :items="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Products', 'url' => route('admin.products.index')],
        ['label' => 'New product'],
    ]" />

    <h1 class="type-h1 mb-6">New product</h1>

    @include('admin.products._form', ['product' => null, 'categories' => $categories])
</x-layouts.admin>

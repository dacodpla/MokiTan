<x-layouts.admin title="Products">
    <div class="flex items-center justify-between mb-6">
        <h1 class="type-h1">Products</h1>
        <a href="{{ route('admin.products.create') }}" class="btn-primary">+ New product</a>
    </div>

    @if ($products->isEmpty())
        <div class="shape-card border border-ink/10 p-14 text-center">
            <div class="type-h3 mb-2">No products yet</div>
            <p class="type-body text-ink/60 mb-6">Start by creating your first product.</p>
            <a href="{{ route('admin.products.create') }}" class="btn-primary">Create product</a>
        </div>
    @else
        <div class="shape-card border border-ink/10 overflow-hidden">
            <table class="w-full text-left type-body">
                <thead class="bg-surface/50">
                    <tr>
                        <th class="p-3">Name</th>
                        <th class="p-3">Category</th>
                        <th class="p-3">Brand</th>
                        <th class="p-3 text-right">Price</th>
                        <th class="p-3 text-right">Stock</th>
                        <th class="p-3">Featured</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr class="border-t border-ink/10">
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    @if ($product->image_path)
                                        <img src="{{ asset($product->image_path) }}" alt=""
                                             class="h-10 w-10 rounded object-cover bg-surface">
                                    @else
                                        <div class="h-10 w-10 rounded bg-surface"></div>
                                    @endif
                                    <span class="font-medium">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="p-3">{{ $product->category?->name ?? '—' }}</td>
                            <td class="p-3">{{ $product->brand }}</td>
                            <td class="p-3 text-right font-bold">{{ \App\Support\Money::idr($product->price) }}</td>
                            <td class="p-3 text-right">{{ $product->stock }}</td>
                            <td class="p-3">
                                @if ($product->is_featured)
                                    <span class="inline-block bg-brand text-canvas px-2 py-0.5 text-xs font-bold rounded">FEATURED</span>
                                @endif
                            </td>
                            <td class="p-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-brand hover:underline mr-3">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                      class="inline" onsubmit="return confirm('Delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-ink/50 hover:text-brand">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    @endif
</x-layouts.admin>

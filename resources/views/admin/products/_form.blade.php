@props(['product' => null, 'categories'])

@php
    $isEdit = (bool) $product;
    $val = fn (string $key, mixed $fallback = null) => old($key, $product?->{$key} ?? $fallback);
@endphp

<form method="POST"
      action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}"
      enctype="multipart/form-data"
      class="grid gap-6 lg:grid-cols-[1fr_320px]">
    @csrf
    @if ($isEdit) @method('PATCH') @endif

    {{-- Left column: main fields --}}
    <div class="space-y-5 shape-card border border-ink/10 p-6">
        <label class="block">
            <span class="type-body font-bold">Name</span>
            <input type="text" name="name" value="{{ $val('name') }}" required
                   class="input-underline mt-1">
            @error('name') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
        </label>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="type-body font-bold">Brand</span>
                <input type="text" name="brand" value="{{ $val('brand') }}" required
                       class="input-underline mt-1">
                @error('brand') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
            </label>
            <label class="block">
                <span class="type-body font-bold">Category</span>
                <select name="category_id" required class="input-underline mt-1">
                    <option value="">Choose…</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected($val('category_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="type-body font-bold">Price (IDR, whole rupiah)</span>
                <input type="number" name="price" value="{{ $val('price', 0) }}" min="0" required
                       class="input-underline mt-1">
                @error('price') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
            </label>
            <label class="block">
                <span class="type-body font-bold">Stock</span>
                <input type="number" name="stock" value="{{ $val('stock', 0) }}" min="0" required
                       class="input-underline mt-1">
                @error('stock') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
            </label>
        </div>

        <label class="block">
            <span class="type-body font-bold">Description</span>
            <textarea name="description" rows="4" class="input-underline mt-1">{{ $val('description') }}</textarea>
            @error('description') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
        </label>

        <label class="block">
            <span class="type-body font-bold">Mold history</span>
            <textarea name="mold_history" rows="3" class="input-underline mt-1">{{ $val('mold_history') }}</textarea>
            @error('mold_history') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Specifications: key/value editor --}}
        <div>
            <span class="type-body font-bold block mb-2">Specifications</span>
            @php $specs = $val('specifications') ?? []; @endphp
            <div id="specs-list" class="space-y-2">
                @foreach ($specs as $k => $v)
                    <div class="grid grid-cols-[1fr_1fr_auto] gap-2">
                        <input type="text" name="specifications[keys][]" value="{{ $k }}"
                               placeholder="e.g. scale" class="input-underline">
                        <input type="text" name="specifications[values][]" value="{{ $v }}"
                               placeholder="e.g. 1/48" class="input-underline">
                        <button type="button" onclick="this.closest('div').remove()"
                                class="text-ink/50 hover:text-brand px-2">✕</button>
                    </div>
                @endforeach
            </div>
            <button type="button" onclick="addSpecRow()" class="mt-3 text-brand hover:underline text-sm">+ Add spec</button>
        </div>
    </div>

    {{-- Right column: image + flags + submit --}}
    <div class="space-y-5">
        <div class="shape-card border border-ink/10 p-6 space-y-4">
            <label class="block">
                <span class="type-body font-bold">Product image</span>
                @if ($isEdit && $product->image_path)
                    <img src="{{ asset($product->image_path) }}" alt=""
                         class="mt-2 h-40 w-full object-cover bg-surface shape-card">
                @endif
                <input type="file" name="image" accept="image/*" class="mt-2 block w-full text-sm">
                @error('image') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-ink/50 mt-1">Max 4MB. Leave blank to keep current.</p>
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_featured" value="1" @checked($val('is_featured'))
                       class="rounded border-ink/40 text-brand focus:ring-brand">
                <span class="type-body font-bold">Featured on homepage</span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full">
            {{ $isEdit ? 'Save changes' : 'Create product' }}
        </button>
        <a href="{{ route('admin.products.index') }}" class="block text-center type-body text-ink/60 hover:text-brand">Cancel</a>
    </div>
</form>

<template id="spec-row-template">
    <div class="grid grid-cols-[1fr_1fr_auto] gap-2">
        <input type="text" name="specifications[keys][]" placeholder="key" class="input-underline">
        <input type="text" name="specifications[values][]" placeholder="value" class="input-underline">
        <button type="button" onclick="this.closest('div').remove()" class="text-ink/50 hover:text-brand px-2">✕</button>
    </div>
</template>
<script>
    function addSpecRow() {
        const tpl = document.getElementById('spec-row-template');
        document.getElementById('specs-list').appendChild(tpl.content.cloneNode(true));
    }
</script>

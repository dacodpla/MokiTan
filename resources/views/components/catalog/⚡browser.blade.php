<?php

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'cat')]
    public string $category = '';

    #[Url(as: 'brand')]
    public string $brand = '';

    #[Url(as: 'sort')]
    public string $sort = 'newest';

    public function mount(?string $categorySlug = null): void
    {
        if ($categorySlug) {
            $this->category = $categorySlug;
        }
    }

    public function updating(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'category', 'brand', 'sort']);
    }

    public function with(): array
    {
        $query = Product::query()->with('category');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('brand', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->category !== '') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $this->category));
        }

        if ($this->brand !== '') {
            $query->where('brand', $this->brand);
        }

        $query = match ($this->sort) {
            'price-low' => $query->orderBy('price'),
            'price-high' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        return [
            'products' => $query->paginate(9),
            'categories' => Category::orderBy('sort_order')->get(),
            'brands' => Product::query()->distinct()->orderBy('brand')->pluck('brand'),
        ];
    }
};
?>

<div class="container mx-auto py-10">
    <x-store.section-heading title="{{ $category ? ucfirst($category) : 'All products' }} ({{ $products->total() }})" />

    <div class="mt-8 grid gap-8 lg:grid-cols-[260px_1fr]">
        <aside class="space-y-6">
            <div>
                <label class="type-body font-bold mb-2 block">Search</label>
                <input wire:model.live.debounce.400ms="search" type="search"
                       placeholder="Kits, brands..." class="input-underline">
            </div>

            <div>
                <label class="type-body font-bold mb-2 block">Category</label>
                <select wire:model.live="category" class="input-underline">
                    <option value="">All</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->slug }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="type-body font-bold mb-2 block">Brand</label>
                <select wire:model.live="brand" class="input-underline">
                    <option value="">Any</option>
                    @foreach ($brands as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="type-body font-bold mb-2 block">Sort</label>
                <select wire:model.live="sort" class="input-underline">
                    <option value="newest">Newest</option>
                    <option value="price-low">Price: low to high</option>
                    <option value="price-high">Price: high to low</option>
                    <option value="name">Name (A–Z)</option>
                </select>
            </div>

            <button wire:click="clearFilters" type="button" class="type-body text-brand hover:underline">
                Clear all filters
            </button>
        </aside>

        <div>
            @if ($products->isEmpty())
                <div class="rounded-xl border border-ink/10 p-10 text-center">
                    <div class="type-h4 mb-2">No products match your filters.</div>
                    <button wire:click="clearFilters" class="btn-primary mt-4">Reset filters</button>
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($products as $product)
                        <x-store.product-card :product="$product" />
                    @endforeach
                </div>
                <div class="mt-8">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</div>

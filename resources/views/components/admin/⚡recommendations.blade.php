<?php

use App\Models\Product;
use Livewire\Component;

new class extends Component
{
    public Product $product;

    public string $search = '';

    public string $categoryFilter = '';

    /** @var array<int, int> */
    public array $linkedIds = [];

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->linkedIds = $product->recommendations()->pluck('products.id')->all();
    }

    public function toggle(int $productId): void
    {
        if (in_array($productId, $this->linkedIds, true)) {
            $this->linkedIds = array_values(array_diff($this->linkedIds, [$productId]));
        } else {
            $this->linkedIds[] = $productId;
        }
        $this->product->recommendations()->sync($this->linkedIds);
    }

    public function with(): array
    {
        $query = Product::query()
            ->where('id', '!=', $this->product->id)
            ->with('category');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('brand', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->categoryFilter !== '') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $this->categoryFilter));
        }

        return [
            'candidates' => $query->orderBy('name')->limit(60)->get(),
            'categories' => \App\Models\Category::orderBy('sort_order')->get(),
            'linkedCount' => count($this->linkedIds),
        ];
    }
};
?>

<div class="shape-card border border-ink/10 p-6">
    <div class="flex items-baseline justify-between mb-4">
        <div class="type-h5">Linked add-ons ({{ $linkedCount }})</div>
        <div class="type-body text-ink/60">Toggle a checkbox to save instantly.</div>
    </div>

    <div class="grid gap-3 md:grid-cols-2 mb-4">
        <input type="search" wire:model.live.debounce.300ms="search"
               placeholder="Search other products by name or brand..."
               class="input-underline">
        <select wire:model.live="categoryFilter" class="input-underline">
            <option value="">All categories</option>
            @foreach ($categories as $c)
                <option value="{{ $c->slug }}">{{ $c->name }}</option>
            @endforeach
        </select>
    </div>

    @if ($candidates->isEmpty())
        <p class="type-body text-ink/60 py-6 text-center">
            No other products match — create more products first.
        </p>
    @else
        <ul class="divide-y divide-ink/10 max-h-96 overflow-auto">
            @foreach ($candidates as $c)
                @php $checked = in_array($c->id, $linkedIds, true); @endphp
                <li wire:key="rec-{{ $c->id }}">
                    <label class="flex items-center gap-3 py-3 cursor-pointer">
                        <input type="checkbox" wire:click="toggle({{ $c->id }})" @checked($checked)
                               class="rounded border-ink/40 text-brand focus:ring-brand">
                        @if ($c->image_path)
                            <img src="{{ asset($c->image_path) }}" alt=""
                                 class="h-10 w-10 rounded object-cover bg-surface">
                        @else
                            <div class="h-10 w-10 rounded bg-surface"></div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="type-body font-medium truncate">{{ $c->name }}</div>
                            <div class="type-body text-ink/60 text-xs">
                                {{ $c->brand }} · {{ $c->category?->name ?? '—' }}
                            </div>
                        </div>
                        <div class="type-body font-bold whitespace-nowrap">{{ \App\Support\Money::idr($c->price) }}</div>
                    </label>
                </li>
            @endforeach
        </ul>
    @endif
</div>

<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Livewire\Component;

new class extends Component
{
    public Product $product;

    public bool $open = false;

    /** @var array<int, int> */
    public array $selected = [];

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function toggle(int $id): void
    {
        if (in_array($id, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$id]));
        } else {
            $this->selected[] = $id;
        }
    }

    public function bundleTotal(): int
    {
        $extras = Product::whereIn('id', $this->selected)->sum('price');

        return (int) ($this->product->price + $extras);
    }

    public function addBundle(): void
    {
        $cart = Cart::current();

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'bundle_meta' => [
                'label' => 'Quick Added',
                'extras' => Product::whereIn('id', $this->selected)
                    ->get(['id', 'name', 'price'])
                    ->toArray(),
            ],
        ]);

        foreach ($this->selected as $extraId) {
            $existing = $cart->items()->where('product_id', $extraId)->whereNull('bundle_meta')->first();
            if ($existing) {
                $existing->increment('quantity');
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $extraId,
                    'quantity' => 1,
                ]);
            }
        }

        $this->reset('selected');
        $this->open = false;
        $this->dispatch('cart:updated');
    }
};
?>

<div>
    <button wire:click="$set('open', true)" type="button" class="btn-outline w-full">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Quick Add — Kit + recommended paints
    </button>

    @if ($open)
        <div class="fixed inset-0 z-50 bg-ink/60 grid place-items-center p-4"
             wire:click.self="$set('open', false)">
            <div class="w-full max-w-lg rounded-xl bg-canvas p-6 shadow-lg">
                <div class="flex items-center justify-between mb-5">
                    <div class="type-h4">Build a bundle</div>
                    <button wire:click="$set('open', false)" class="text-ink/50 hover:text-brand text-2xl">×</button>
                </div>

                <div class="rounded-xl border border-ink/10 p-4 mb-4">
                    <div class="type-body font-bold">{{ $product->name }}</div>
                    <div class="type-body text-ink/60">{{ \App\Support\Money::idr($product->price) }} — the kit</div>
                </div>

                @if ($product->recommendations->isNotEmpty())
                    <div class="type-body font-bold mb-2">Recommended add-ons</div>
                    <ul class="space-y-2 mb-6 max-h-64 overflow-auto">
                        @foreach ($product->recommendations as $rec)
                            <li>
                                <label class="flex items-center gap-3 p-3 rounded-lg border border-ink/10 cursor-pointer hover:border-brand">
                                    <input type="checkbox"
                                           wire:click="toggle({{ $rec->id }})"
                                           @checked(in_array($rec->id, $selected, true))
                                           class="rounded border-ink/40 text-brand focus:ring-brand">
                                    <span class="flex-1">
                                        <span class="type-body font-medium">{{ $rec->name }}</span>
                                        <span class="block type-body text-ink/60">{{ $rec->brand }}</span>
                                    </span>
                                    <span class="type-body font-bold">{{ \App\Support\Money::idr($rec->price) }}</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="type-body text-ink/60 mb-4">No add-ons available for this kit.</p>
                @endif

                <div class="flex items-center justify-between border-t border-ink/10 pt-4">
                    <div>
                        <div class="type-body text-ink/60">Bundle total</div>
                        <div class="type-h4">{{ \App\Support\Money::idr($this->bundleTotal()) }}</div>
                    </div>
                    <button wire:click="addBundle" class="btn-primary">Add bundle to cart</button>
                </div>
            </div>
        </div>
    @endif
</div>

<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Livewire\Component;

new class extends Component
{
    public Product $product;

    public int $quantity = 1;

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function increment(): void
    {
        $this->quantity++;
    }

    public function decrement(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function add(): void
    {
        $cart = Cart::current();
        $existing = $cart->items()
            ->where('product_id', $this->product->id)
            ->whereNull('bundle_meta')
            ->first();

        if ($existing) {
            $existing->increment('quantity', $this->quantity);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $this->product->id,
                'quantity' => $this->quantity,
            ]);
        }

        $this->dispatch('cart:updated');
        $this->dispatch('added-to-cart', name: $this->product->name);
    }
};
?>

<div>
    <div class="flex items-center gap-3">
        <div class="inline-flex items-center border border-ink/20 rounded-pill">
            <button type="button" wire:click="decrement" class="h-10 w-10 grid place-content-center hover:text-brand">−</button>
            <span class="w-8 text-center type-body font-bold">{{ $quantity }}</span>
            <button type="button" wire:click="increment" class="h-10 w-10 grid place-content-center hover:text-brand">+</button>
        </div>
        <button wire:click="add" type="button" class="btn-primary">
            Add to Cart
        </button>
    </div>
    <div x-data="{ shown: false }"
         x-on:added-to-cart.window="shown = true; setTimeout(() => shown = false, 2200)"
         x-show="shown" x-cloak
         class="mt-3 type-body text-brand">
        Added to cart ✓
    </div>
</div>

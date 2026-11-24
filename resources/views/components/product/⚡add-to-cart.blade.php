<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
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
        if ($this->quantity < $this->product->stock) {
            $this->quantity++;
        }
    }

    public function decrement(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function add(): void
    {
        if (! Auth::check()) {
            session()->put('url.intended', url()->previous());
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->resetErrorBag();

        $product = $this->product->fresh();
        $cart = Cart::current();
        $inCart = $cart->quantityOf($product->id);
        $room = $product->stock - $inCart;

        if ($product->stock < 1) {
            $this->addError('quantity', 'This product is out of stock.');

            return;
        }

        if ($this->quantity > $room) {
            $this->addError('quantity', $room < 1
                ? "You already have all {$product->stock} available unit(s) in your cart."
                : "Only {$room} more unit(s) can be added (stock: {$product->stock}, in your cart: {$inCart}).");

            return;
        }

        $existing = $cart->items()
            ->where('product_id', $product->id)
            ->whereNull('bundle_meta')
            ->whereNull('parent_id')
            ->first();

        if ($existing) {
            $existing->increment('quantity', $this->quantity);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $this->quantity,
            ]);
        }

        $this->dispatch('cart:updated');
        $this->dispatch('added-to-cart', name: $product->name);
    }
};
?>

<div>
    @if ($product->stock < 1)
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center rounded-pill bg-surface px-4 py-2 type-body font-bold">Stok Habis</span>
            <button type="button" disabled class="btn-primary opacity-40 cursor-not-allowed">Add to Cart</button>
        </div>
    @else
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
        @error('quantity')
            <p class="mt-3 type-body text-sm text-brand" role="alert">{{ $message }}</p>
        @enderror
        <div x-data="{ shown: false }"
             x-on:added-to-cart.window="shown = true; setTimeout(() => shown = false, 2200)"
             x-show="shown" x-cloak
             class="mt-3 type-body text-brand">
            Added to cart ✓
        </div>
    @endif
</div>

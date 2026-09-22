<?php

use App\Models\Cart;
use App\Models\CartItem;
use Livewire\Component;

new class extends Component
{
    public Cart $cart;

    public function mount(): void
    {
        $this->cart = Cart::current()->load('items.product');
    }

    public function refreshCart(): void
    {
        $this->cart = Cart::current()->load('items.product');
    }

    public function increment(int $itemId): void
    {
        CartItem::where('id', $itemId)->where('cart_id', $this->cart->id)->increment('quantity');
        $this->refreshCart();
        $this->dispatch('cart:updated');
    }

    public function decrement(int $itemId): void
    {
        $item = $this->cart->items()->find($itemId);
        if (! $item) {
            return;
        }
        if ($item->quantity <= 1) {
            $item->delete();
        } else {
            $item->decrement('quantity');
        }
        $this->refreshCart();
        $this->dispatch('cart:updated');
    }

    public function remove(int $itemId): void
    {
        $this->cart->items()->where('id', $itemId)->delete();
        $this->refreshCart();
        $this->dispatch('cart:updated');
    }

    public function checkout(): void
    {
        if ($this->cart->items()->count() === 0) {
            $this->redirect(route('catalog'), navigate: true);

            return;
        }

        $this->redirect(route('checkout'), navigate: true);
    }
};
?>

<div class="container mx-auto py-10">
    <x-store.section-heading title="Your Cart" />

    @if ($cart->items->isEmpty())
        <div class="mt-10 rounded-xl border border-ink/10 p-14 text-center">
            <div class="type-h3 mb-3">Your cart is empty</div>
            <p class="type-body text-ink/60 mb-6">Browse the catalog to find your next build.</p>
            <a href="{{ route('catalog') }}" class="btn-primary">Back to Catalog</a>
        </div>
    @else
        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_360px]">
            <div class="space-y-4">
                @foreach ($cart->items as $item)
                    <div wire:key="item-{{ $item->id }}"
                         class="grid grid-cols-[100px_1fr_auto] gap-4 rounded-xl border border-ink/10 p-4">
                        <img src="{{ asset($item->product->image_path) }}" alt="{{ $item->product->name }}"
                             class="h-24 w-24 rounded-lg object-cover bg-surface">
                        <div class="min-w-0">
                            <div class="type-body text-ink/60 uppercase">{{ $item->product->brand }}</div>
                            <div class="type-h5 truncate">{{ $item->product->name }}</div>
                            @if ($item->bundle_meta && isset($item->bundle_meta['label']))
                                <span class="inline-block mt-1 rounded-pill bg-brand/10 text-brand px-2 py-0.5 text-xs font-bold">
                                    {{ $item->bundle_meta['label'] }}
                                </span>
                            @endif
                            <div class="mt-2 type-body font-bold">{{ \App\Support\Money::idr($item->product->price) }}</div>

                            <div class="mt-3">
                                <livewire:product.gift-toggle :item="$item" :key="'gift-'.$item->id" />
                            </div>
                        </div>
                        <div class="flex flex-col items-end justify-between">
                            <button wire:click="remove({{ $item->id }})"
                                    class="text-ink/50 hover:text-brand text-sm">Remove</button>
                            <div class="inline-flex items-center border border-ink/20 rounded-pill">
                                <button wire:click="decrement({{ $item->id }})" class="h-9 w-9 grid place-content-center hover:text-brand">−</button>
                                <span class="w-8 text-center type-body font-bold">{{ $item->quantity }}</span>
                                <button wire:click="increment({{ $item->id }})" class="h-9 w-9 grid place-content-center hover:text-brand">+</button>
                            </div>
                            <div class="type-body font-bold">{{ \App\Support\Money::idr($item->lineTotal()) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <aside class="h-fit rounded-xl border border-ink/10 p-6 sticky top-6">
                <div class="type-h4 mb-4">Order Summary</div>
                <dl class="space-y-2 type-body">
                    <div class="flex justify-between">
                        <dt class="text-ink/60">Subtotal</dt>
                        <dd class="font-bold">{{ \App\Support\Money::idr($cart->subtotal()) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-ink/60">Shipping</dt>
                        <dd class="text-ink/60">Calculated at checkout</dd>
                    </div>
                </dl>
                <button wire:click="checkout" class="btn-primary w-full mt-6">Checkout</button>
                <a href="{{ route('catalog') }}" class="mt-3 block text-center type-body text-brand hover:underline">Continue shopping</a>
            </aside>
        </div>
    @endif
</div>

<?php

use App\Models\Cart;
use Livewire\Component;

new class extends Component
{
    public Cart $cart;

    public function mount(): void
    {
        $this->cart = Cart::current()->load('lines.product', 'lines.children.product');
    }

    public function refreshCart(): void
    {
        $this->cart = Cart::current()->load('lines.product', 'lines.children.product');
    }

    public function increment(int $itemId): void
    {
        $this->resetErrorBag();

        $item = $this->cart->lines()->with('product')->find($itemId);

        // Bundles are added whole, so their quantity is fixed.
        if (! $item || $item->children()->exists()) {
            return;
        }

        $product = $item->product;
        $inCart = $this->cart->quantityOf($product->id);

        if ($inCart >= $product->stock) {
            $this->addError('stock', "Only {$product->stock} unit(s) of {$product->name} in stock.");

            return;
        }

        $item->increment('quantity');
        $this->refreshCart();
        $this->dispatch('cart:updated');
    }

    public function decrement(int $itemId): void
    {
        $item = $this->cart->lines()->find($itemId);
        if (! $item || $item->children()->exists()) {
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
    <x-store.breadcrumb :items="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Cart'],
    ]" />

    <x-store.section-heading title="Your Cart" />

    @if ($cart->lines->isEmpty())
        <div class="mt-10 rounded-xl border border-ink/10 p-14 text-center">
            <div class="type-h3 mb-3">Your cart is empty</div>
            <p class="type-body text-ink/60 mb-6">Browse the catalog to find your next build.</p>
            <a href="{{ route('catalog') }}" class="btn-primary">Back to Catalog</a>
        </div>
    @else
        @error('stock')
            <div class="mt-6 shape-card border border-brand bg-brand/5 p-4 type-body text-brand" role="alert">{{ $message }}</div>
        @enderror

        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_360px]">
            <div class="space-y-4">
                @foreach ($cart->lines as $item)
                    @php $isBundle = $item->children->isNotEmpty(); @endphp
                    <div wire:key="item-{{ $item->id }}" x-data="{ open: false }"
                         class="rounded-xl border border-ink/10 p-4">
                        <div class="grid grid-cols-[100px_1fr_auto] gap-4">
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

                                @if ($isBundle)
                                    <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open"
                                            class="mt-3 inline-flex items-center gap-2 type-body text-brand hover:underline">
                                        <svg class="h-4 w-4 transition-transform" x-bind:class="open && 'rotate-90'"
                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
                                        <span x-text="open ? 'Hide' : 'Show'"></span>
                                        {{ $item->children->count() }} quick-added {{ \Illuminate\Support\Str::plural('item', $item->children->count()) }}
                                    </button>
                                @endif

                                <div class="mt-3">
                                    <livewire:product.gift-toggle :item="$item" :key="'gift-'.$item->id" />
                                </div>
                            </div>
                            <div class="flex flex-col items-end justify-between">
                                <button wire:click="remove({{ $item->id }})"
                                        class="text-ink/50 hover:text-brand text-sm">{{ $isBundle ? 'Remove all' : 'Remove' }}</button>
                                @unless ($isBundle)
                                    <div class="inline-flex items-center border border-ink/20 rounded-pill">
                                        <button wire:click="decrement({{ $item->id }})" class="h-9 w-9 grid place-content-center hover:text-brand">−</button>
                                        <span class="w-8 text-center type-body font-bold">{{ $item->quantity }}</span>
                                        <button wire:click="increment({{ $item->id }})" class="h-9 w-9 grid place-content-center hover:text-brand">+</button>
                                    </div>
                                @endunless
                                <div class="type-body font-bold">{{ \App\Support\Money::idr($item->bundleTotal()) }}</div>
                            </div>
                        </div>

                        @if ($isBundle)
                            <ul x-show="open" x-cloak x-transition
                                class="mt-4 ml-6 space-y-2 border-l-2 border-brand/30 pl-4">
                                @foreach ($item->children as $child)
                                    <li wire:key="child-{{ $child->id }}" class="flex items-center gap-3">
                                        <img src="{{ asset($child->product->image_path) }}" alt=""
                                             class="h-10 w-10 rounded object-cover bg-surface">
                                        <div class="flex-1 min-w-0">
                                            <div class="type-body font-medium truncate">{{ $child->product->name }}</div>
                                            <div class="type-body text-ink/60 text-xs">{{ $child->product->brand }}</div>
                                        </div>
                                        <div class="type-body font-bold whitespace-nowrap">{{ \App\Support\Money::idr($child->lineTotal()) }}</div>
                                        <button wire:click="remove({{ $child->id }})" aria-label="Remove {{ $child->product->name }}"
                                                class="text-ink/40 hover:text-brand text-xl leading-none">×</button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
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

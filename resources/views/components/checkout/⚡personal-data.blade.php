<?php

use App\Enums\Courier;
use App\Models\Cart;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
    public string $recipient_name = '';

    public string $phone = '';

    public string $line1 = '';

    public string $line2 = '';

    public string $city = '';

    public string $province = '';

    public string $postal_code = '';

    public string $courier = '';

    public function mount(): void
    {
        $user = Auth::user();
        $address = $user->defaultAddress();

        $this->recipient_name = $address->recipient_name ?? $user->name;
        $this->phone = $address->phone ?? $user->phone ?? '';
        $this->line1 = $address->line1 ?? '';
        $this->line2 = $address->line2 ?? '';
        $this->city = $address->city ?? '';
        $this->province = $address->province ?? '';
        $this->postal_code = $address->postal_code ?? '';
        $this->courier = Courier::JneReguler->value;
    }

    /**
     * Validation errors thrown by CheckoutService (empty cart, not enough stock)
     * are caught by Livewire and shown through @error, so the form keeps its input.
     */
    public function placeOrder(CheckoutService $checkout): void
    {
        $validated = $this->validate([
            'recipient_name' => 'required|string|max:120',
            'phone' => 'required|string|max:30',
            'line1' => 'required|string|max:200',
            'line2' => 'nullable|string|max:200',
            'city' => 'required|string|max:80',
            'province' => 'required|string|max:80',
            'postal_code' => 'required|string|max:20',
            'courier' => ['required', Rule::enum(Courier::class)],
        ]);

        $shipping = collect($validated)->except('courier')->all();

        $order = $checkout->placeOrder(
            Auth::user(),
            Cart::current(),
            $shipping,
            Courier::from($validated['courier']),
        );

        Auth::user()->addresses()->updateOrCreate(['is_default' => true], $shipping);

        $this->dispatch('cart:updated');
        $this->redirect(route('orders.success', $order), navigate: true);
    }

    public function with(): array
    {
        return [
            'cart' => Cart::current()->load('items.product'),
            'couriers' => Courier::cases(),
            'shippingFee' => Courier::tryFrom($this->courier)?->fee() ?? 0,
        ];
    }
};
?>

<div class="container mx-auto py-10">
    <x-store.breadcrumb :items="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Cart', 'url' => route('cart')],
        ['label' => 'Checkout'],
    ]" />

    <x-store.section-heading title="Shipping & Personal Data" />

    <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_360px]">
        <form wire:submit.prevent="placeOrder" class="space-y-5 rounded-xl border border-ink/10 p-6">
            @if ($errors->has('stock') || $errors->has('cart'))
                <div class="shape-card border border-brand bg-brand/5 p-4 type-body text-brand" role="alert">
                    @error('stock') <p>{{ $message }}</p> @enderror
                    @error('cart') <p>{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="type-body font-bold">Recipient name</span>
                    <input type="text" wire:model="recipient_name" class="input-underline mt-1">
                    @error('recipient_name') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
                </label>
                <label class="block">
                    <span class="type-body font-bold">Phone</span>
                    <input type="tel" wire:model="phone" class="input-underline mt-1">
                    @error('phone') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
                </label>
            </div>

            <label class="block">
                <span class="type-body font-bold">Address line 1</span>
                <input type="text" wire:model="line1" class="input-underline mt-1">
                @error('line1') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
            </label>
            <label class="block">
                <span class="type-body font-bold">Address line 2 <span class="text-ink/40 font-normal">(optional)</span></span>
                <input type="text" wire:model="line2" class="input-underline mt-1">
            </label>

            <div class="grid gap-4 md:grid-cols-3">
                <label class="block">
                    <span class="type-body font-bold">City</span>
                    <input type="text" wire:model="city" class="input-underline mt-1">
                    @error('city') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
                </label>
                <label class="block">
                    <span class="type-body font-bold">Province</span>
                    <input type="text" wire:model="province" class="input-underline mt-1">
                    @error('province') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
                </label>
                <label class="block">
                    <span class="type-body font-bold">Postal code</span>
                    <input type="text" wire:model="postal_code" class="input-underline mt-1">
                    @error('postal_code') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
                </label>
            </div>

            <label class="block">
                <span class="type-body font-bold">Courier</span>
                <select wire:model.live="courier" class="input-underline mt-1">
                    @foreach ($couriers as $option)
                        <option value="{{ $option->value }}">
                            {{ $option->value }} — {{ \App\Support\Money::idr($option->fee()) }}
                        </option>
                    @endforeach
                </select>
                @error('courier') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
            </label>

            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="placeOrder">
                <span wire:loading.remove wire:target="placeOrder">Place order</span>
                <span wire:loading wire:target="placeOrder">Placing order…</span>
            </button>
            <p class="text-xs text-ink/50 text-center">
                Your order starts as <strong>Pending</strong> until the seller confirms your payment.
            </p>
        </form>

        <aside class="h-fit rounded-xl border border-ink/10 p-6 sticky top-6 space-y-4">
            <div class="type-h4">Order Summary</div>
            <ul class="space-y-2 text-sm">
                @foreach ($cart->items as $item)
                    <li class="flex justify-between gap-2">
                        <span>{{ $item->product->name }} × {{ $item->quantity }}</span>
                        <span class="font-bold">{{ \App\Support\Money::idr($item->lineTotal()) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="border-t border-ink/10 pt-3 flex justify-between type-body">
                <span>Subtotal</span><span class="font-bold">{{ \App\Support\Money::idr($cart->subtotal()) }}</span>
            </div>
            <div class="flex justify-between type-body">
                <span>Shipping</span><span class="font-bold">{{ \App\Support\Money::idr($shippingFee) }}</span>
            </div>
            <div class="flex justify-between type-h4 border-t border-ink/10 pt-3">
                <span>Total</span><span>{{ \App\Support\Money::idr($cart->subtotal() + $shippingFee) }}</span>
            </div>
        </aside>
    </div>
</div>

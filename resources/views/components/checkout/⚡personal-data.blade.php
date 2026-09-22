<?php

use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
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

    public bool $showPayment = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $addr = Auth::user()->defaultAddress();
            $this->recipient_name = $addr->recipient_name ?? Auth::user()->name;
            $this->phone = $addr->phone ?? Auth::user()->phone ?? '';
            $this->line1 = $addr->line1 ?? '';
            $this->line2 = $addr->line2 ?? '';
            $this->city = $addr->city ?? '';
            $this->province = $addr->province ?? '';
            $this->postal_code = $addr->postal_code ?? '';
        }
    }

    public function proceedToPayment(): void
    {
        $this->validate([
            'recipient_name' => 'required|string|max:120',
            'phone' => 'required|string|max:30',
            'line1' => 'required|string|max:200',
            'city' => 'required|string|max:80',
            'province' => 'required|string|max:80',
            'postal_code' => 'required|string|max:20',
        ]);

        if (Auth::check()) {
            Auth::user()->addresses()->updateOrCreate(
                ['is_default' => true],
                [
                    'recipient_name' => $this->recipient_name,
                    'phone' => $this->phone,
                    'line1' => $this->line1,
                    'line2' => $this->line2,
                    'city' => $this->city,
                    'province' => $this->province,
                    'postal_code' => $this->postal_code,
                ],
            );
        }

        session(['checkout.shipping' => [
            'recipient_name' => $this->recipient_name,
            'phone' => $this->phone,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'province' => $this->province,
            'postal_code' => $this->postal_code,
        ]]);

        $this->showPayment = true;
    }

    public function with(): array
    {
        return [
            'cart' => Cart::current()->load('items.product'),
        ];
    }
};
?>

<div class="container mx-auto py-10">
    <a href="{{ route('cart') }}" class="type-body text-brand hover:underline inline-flex items-center gap-1">
        ← Back to Cart
    </a>

    <x-store.section-heading title="Shipping & Personal Data" />

    <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_360px]">
        <form wire:submit.prevent="proceedToPayment" class="space-y-5 rounded-xl border border-ink/10 p-6">
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

            <button type="submit" class="btn-primary w-full">Continue to payment</button>
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
                <span>Shipping</span><span class="font-bold">{{ \App\Support\Money::idr(20000) }}</span>
            </div>
            <div class="flex justify-between type-h4 border-t border-ink/10 pt-3">
                <span>Total</span><span>{{ \App\Support\Money::idr($cart->subtotal() + 20000) }}</span>
            </div>
        </aside>
    </div>

    @if ($showPayment)
        <livewire:checkout.payment />
    @endif
</div>

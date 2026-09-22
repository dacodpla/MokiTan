<?php

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PaymentGateway;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    public string $step = 'method';

    public string $method = 'card';

    public string $card_number = '';

    public string $expiration = '';

    public string $cvc = '';

    public ?string $errorMessage = null;

    public function chooseMethod(string $method): void
    {
        $this->method = $method;
        $this->step = 'card';
    }

    public function pay(PaymentGateway $gateway): void
    {
        $this->validate([
            'card_number' => 'required|digits_between:12,19',
            'expiration' => 'required|regex:/^\d{2}\/\d{2}$/',
            'cvc' => 'required|digits:3',
        ]);

        $cart = Cart::current()->load('items.product');
        if ($cart->items->isEmpty()) {
            $this->redirect(route('cart'), navigate: true);

            return;
        }

        $shipping = session('checkout.shipping', []);
        $subtotal = $cart->subtotal();
        $shippingFee = 20000;
        $total = $subtotal + $shippingFee;

        $order = DB::transaction(function () use ($cart, $shipping, $subtotal, $shippingFee, $total) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => 'MKT-'.strtoupper(Str::random(8)),
                'status' => OrderStatus::Pending->value,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $total,
                'payment_method' => $this->method,
                'shipping_snapshot' => $shipping,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_snapshot' => [
                        'name' => $item->product->name,
                        'brand' => $item->product->brand,
                        'price' => $item->product->price,
                        'image_path' => $item->product->image_path,
                        'bundle_meta' => $item->bundle_meta,
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => $item->product->price,
                    'is_gift' => $item->is_gift,
                    'gift_note' => $item->gift_note,
                ]);
            }

            return $order;
        });

        $result = $gateway->charge($order, [
            'number' => $this->card_number,
            'exp' => $this->expiration,
            'cvc' => $this->cvc,
        ]);

        if (! $result->succeeded) {
            $order->update(['status' => OrderStatus::Failed->value]);
            $this->errorMessage = $result->message ?? 'Payment failed. Please try again.';

            return;
        }

        $order->update([
            'status' => OrderStatus::Paid->value,
            'tracking_number' => 'MKT-'.$order->id.'-'.strtoupper(Str::random(6)),
        ]);

        $cart->items()->delete();
        session()->forget('checkout.shipping');

        $this->redirect(route('orders.success', $order), navigate: true);
    }
};
?>

<div class="fixed inset-0 z-50 bg-ink/70 grid place-items-center p-4">
    <div class="w-full max-w-lg rounded-xl bg-canvas p-6">
        <div class="flex items-center justify-between mb-5">
            <div class="type-h4">Payment Details</div>
            <button type="button" wire:click="$parent.set('showPayment', false)"
                    class="text-ink/50 hover:text-brand text-2xl">×</button>
        </div>

        @if ($errorMessage)
            <div class="mb-4 rounded-lg border border-brand/40 bg-brand/10 p-4 type-body text-brand">
                <div class="font-bold">Gagal — Coba Lagi</div>
                <div>{{ $errorMessage }}</div>
            </div>
        @endif

        @if ($step === 'method')
            <p class="type-body text-ink/60 mb-4">Choose payment method</p>
            <div class="space-y-3">
                <button wire:click="chooseMethod('card')" type="button"
                        class="w-full flex items-center justify-between shape-card border border-ink/10 p-4 hover:border-brand transition">
                    <span class="type-body font-bold">Credit / Debit Card</span>
                    <span class="type-body text-ink/60">Visa · MasterCard</span>
                </button>
                <button wire:click="chooseMethod('mastercard')" type="button"
                        class="w-full flex items-center justify-between shape-card border border-ink/10 p-4 hover:border-brand transition">
                    <span class="type-body font-bold">MasterCard</span>
                    <span class="inline-block rounded bg-ink text-canvas px-2 py-0.5 text-xs font-bold">MC</span>
                </button>
                <button wire:click="chooseMethod('stripe')" type="button"
                        class="w-full flex items-center justify-between shape-card border border-ink/10 p-4 hover:border-brand transition">
                    <span class="type-body font-bold">Stripe</span>
                    <span class="inline-block rounded bg-[#635bff] text-canvas px-2 py-0.5 text-xs font-bold">Stripe</span>
                </button>
            </div>
        @else
            <form wire:submit.prevent="pay" class="space-y-4">
                <label class="block">
                    <span class="type-body font-bold">Card Number</span>
                    <input type="text" inputmode="numeric" maxlength="19"
                           wire:model="card_number" placeholder="1234 5678 9012 3456"
                           class="input-underline mt-1">
                    @error('card_number') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
                </label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="block">
                        <span class="type-body font-bold">Expiration Date</span>
                        <input type="text" wire:model="expiration" placeholder="MM/YY"
                               class="input-underline mt-1">
                        @error('expiration') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
                    </label>
                    <label class="block">
                        <span class="type-body font-bold">CVC</span>
                        <input type="text" inputmode="numeric" maxlength="3"
                               wire:model="cvc" placeholder="123"
                               class="input-underline mt-1">
                        @error('cvc') <p class="text-brand text-xs mt-1">{{ $message }}</p> @enderror
                    </label>
                </div>
                <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="pay">Pay</span>
                    <span wire:loading wire:target="pay">Processing…</span>
                </button>
                <button type="button" wire:click="$set('step', 'method')" class="type-body text-ink/60 hover:text-brand w-full text-center">
                    ← Choose another method
                </button>
                <p class="text-xs text-ink/50 text-center">
                    Demo mode: any card <strong>not</strong> ending in <code>0000</code> succeeds; cards ending <code>0000</code> fail.
                </p>
            </form>
        @endif
    </div>
</div>

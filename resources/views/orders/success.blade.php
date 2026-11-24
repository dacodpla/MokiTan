@php
    $statusMessage = match ($order->status) {
        \App\Enums\OrderStatus::Pending => 'Waiting for the seller to confirm your payment.',
        \App\Enums\OrderStatus::Paid => 'Payment confirmed. Your order is being packed.',
        \App\Enums\OrderStatus::Shipped => 'Your parcel is on its way. Use the tracking number below.',
    };
    $shipping = $order->shipping_snapshot ?? [];
@endphp

<x-layouts.store>
    <x-slot name="title">Invoice {{ $order->order_number }} — Moki-Tan</x-slot>

    <div class="container mx-auto py-16 max-w-2xl">
        <x-store.breadcrumb :items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Pesanan Saya', 'url' => route('orders.index')],
            ['label' => 'Invoice '.$order->order_number],
        ]" />

        <div class="rounded-xl border border-ink/10 p-10 text-center">
            <div class="mx-auto h-20 w-20 rounded-full bg-brand text-canvas grid place-items-center type-h1">✓</div>
            <h1 class="type-h1 mt-6">Order received!</h1>
            <p class="type-body text-ink/60 mt-2">{{ $statusMessage }}</p>

            <div class="mt-4 flex justify-center">
                <x-store.status-badge :status="$order->status" />
            </div>

            <div class="mt-8 rounded-xl bg-surface/40 p-6 text-left space-y-2">
                <div class="flex justify-between type-body"><span>Invoice</span><span class="font-bold">{{ $order->order_number }}</span></div>
                <div class="flex justify-between type-body"><span>Courier</span><span class="font-bold">{{ $order->courier?->value ?? '—' }}</span></div>
                <div class="flex justify-between type-body"><span>Tracking number</span><span class="font-bold">{{ $order->tracking_number ?? '—' }}</span></div>
                <div class="flex justify-between type-body"><span>Subtotal</span><span class="font-bold">{{ \App\Support\Money::idr($order->subtotal) }}</span></div>
                <div class="flex justify-between type-body"><span>Shipping</span><span class="font-bold">{{ \App\Support\Money::idr($order->shipping_fee) }}</span></div>
                <div class="flex justify-between type-h5 border-t border-ink/10 pt-2"><span>Total</span><span>{{ \App\Support\Money::idr($order->total) }}</span></div>
            </div>

            <ul class="mt-6 text-left space-y-2">
                @foreach ($order->items as $item)
                    <li class="flex justify-between type-body">
                        <span>
                            {{ $item->product_snapshot['name'] }} × {{ $item->quantity }}
                            @if ($item->is_gift)
                                <span class="text-brand text-sm">(gift)</span>
                            @endif
                        </span>
                        <span class="font-bold">{{ \App\Support\Money::idr($item->lineTotal()) }}</span>
                    </li>
                @endforeach
            </ul>

            @if ($shipping)
                <div class="mt-6 text-left type-body text-ink-alt/80">
                    <div class="font-bold text-ink">Ship to</div>
                    {{ $shipping['recipient_name'] ?? '' }} · {{ $shipping['phone'] ?? '' }}<br>
                    {{ $shipping['line1'] ?? '' }}@if (! empty($shipping['line2'])), {{ $shipping['line2'] }}@endif,
                    {{ $shipping['city'] ?? '' }}, {{ $shipping['province'] ?? '' }} {{ $shipping['postal_code'] ?? '' }}
                </div>
            @endif

            <div class="mt-10 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('orders.track', $order) }}" class="btn-primary">Pelacakan Paket →</a>
                <a href="{{ route('orders.index') }}" class="btn-outline">Pesanan Saya</a>
            </div>
        </div>
    </div>
</x-layouts.store>

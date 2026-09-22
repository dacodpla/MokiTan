<x-layouts.store>
    <x-slot name="title">Payment Success — Moki-Tan</x-slot>

    <div class="container mx-auto py-16 max-w-2xl">
        <div class="rounded-xl border border-ink/10 p-10 text-center">
            <div class="mx-auto h-20 w-20 rounded-full bg-brand text-canvas grid place-items-center type-h1">✓</div>
            <h1 class="type-h1 mt-6">Payment Success!</h1>
            <p class="type-body text-ink/60 mt-2">Thank you for your order.</p>

            <div class="mt-8 rounded-xl bg-surface/40 p-6 text-left space-y-2">
                <div class="flex justify-between type-body"><span>Order number</span><span class="font-bold">{{ $order->order_number }}</span></div>
                <div class="flex justify-between type-body"><span>Tracking number</span><span class="font-bold">{{ $order->tracking_number }}</span></div>
                <div class="flex justify-between type-body"><span>Total</span><span class="font-bold">{{ \App\Support\Money::idr($order->total) }}</span></div>
            </div>

            <ul class="mt-6 text-left space-y-2">
                @foreach ($order->items as $item)
                    <li class="flex justify-between type-body">
                        <span>{{ $item->product_snapshot['name'] }} × {{ $item->quantity }}</span>
                        <span class="font-bold">{{ \App\Support\Money::idr($item->lineTotal()) }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="mt-10 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('orders.track', $order) }}" class="btn-primary">Pelacakan Paket →</a>
                <a href="{{ route('home') }}" class="btn-outline">Back to Homepage</a>
            </div>
        </div>
    </div>
</x-layouts.store>

@php
    $steps = [
        \App\Enums\OrderStatus::Pending,
        \App\Enums\OrderStatus::Paid,
        \App\Enums\OrderStatus::Shipped,
        \App\Enums\OrderStatus::Delivered,
    ];
    $currentIndex = array_search($order->status, $steps, true);
    if ($currentIndex === false) {
        $currentIndex = 0;
    }
@endphp

<x-layouts.store>
    <x-slot name="title">Track — {{ $order->order_number }}</x-slot>

    <div class="container mx-auto py-12 max-w-3xl">
        <a href="{{ route('home') }}" class="type-body text-brand hover:underline">← Back to Homepage</a>

        <h1 class="type-h1 mt-4">Track your package</h1>
        <p class="type-body text-ink/60 mt-2">Order {{ $order->order_number }} · Tracking {{ $order->tracking_number ?? '—' }}</p>

        <ol class="mt-10 space-y-3">
            @foreach ($steps as $i => $step)
                @php $done = $i <= $currentIndex; @endphp
                <li class="flex items-center gap-4 rounded-xl border p-4 {{ $done ? 'border-brand bg-brand/5' : 'border-ink/10' }}">
                    <span class="h-8 w-8 rounded-full grid place-items-center type-body font-bold {{ $done ? 'bg-brand text-canvas' : 'bg-surface text-ink/50' }}">
                        {{ $i + 1 }}
                    </span>
                    <div>
                        <div class="type-body font-bold">{{ $step->label() }}</div>
                        <div class="type-body text-ink/60 text-sm">{{ ucfirst($step->value) }}</div>
                    </div>
                </li>
            @endforeach
        </ol>

        <div class="mt-10 rounded-xl border border-ink/10 p-6">
            <div class="type-h4 mb-3">Shipping to</div>
            @if ($order->shipping_snapshot)
                <address class="not-italic type-body text-ink-alt/80">
                    {{ $order->shipping_snapshot['recipient_name'] ?? '' }}<br>
                    {{ $order->shipping_snapshot['line1'] ?? '' }}
                    @if (! empty($order->shipping_snapshot['line2'])), {{ $order->shipping_snapshot['line2'] }}@endif<br>
                    {{ $order->shipping_snapshot['city'] ?? '' }}, {{ $order->shipping_snapshot['province'] ?? '' }} {{ $order->shipping_snapshot['postal_code'] ?? '' }}<br>
                    {{ $order->shipping_snapshot['phone'] ?? '' }}
                </address>
            @endif
        </div>
    </div>
</x-layouts.store>

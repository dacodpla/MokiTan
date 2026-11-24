<x-layouts.store>
    <x-slot name="title">Pesanan Saya — Moki-Tan</x-slot>

    <div class="container mx-auto py-10">
        <x-store.breadcrumb :items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Pesanan Saya'],
        ]" />

        <x-store.section-heading title="Pesanan Saya" />

        @if ($orders->isEmpty())
            <div class="mt-10 rounded-xl border border-ink/10 p-14 text-center">
                <div class="type-h3 mb-3">Belum ada pesanan</div>
                <p class="type-body text-ink/60 mb-6">Pesanan yang kamu buat akan muncul di sini.</p>
                <a href="{{ route('catalog') }}" class="btn-primary">Mulai belanja</a>
            </div>
        @else
            <ul class="mt-8 space-y-4">
                @foreach ($orders as $order)
                    <li class="shape-card border border-ink/10 p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="type-h5">{{ $order->order_number }}</div>
                                <div class="type-body text-sm text-ink/60">
                                    {{ $order->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                            <x-store.status-badge :status="$order->status" />
                        </div>

                        <ul class="mt-4 space-y-1 type-body text-sm text-ink-alt/80">
                            @foreach ($order->items as $item)
                                <li>{{ $item->product_snapshot['name'] }} × {{ $item->quantity }}</li>
                            @endforeach
                        </ul>

                        <div class="mt-4 flex flex-wrap items-end justify-between gap-4 border-t border-ink/10 pt-4">
                            <dl class="type-body text-sm space-y-1">
                                <div class="flex gap-2"><dt class="text-ink/60">Kurir:</dt><dd class="font-bold">{{ $order->courier?->value ?? '—' }}</dd></div>
                                <div class="flex gap-2"><dt class="text-ink/60">No. resi:</dt><dd class="font-bold">{{ $order->tracking_number ?? '—' }}</dd></div>
                                <div class="flex gap-2"><dt class="text-ink/60">Total:</dt><dd class="font-bold">{{ \App\Support\Money::idr($order->total) }}</dd></div>
                            </dl>
                            <a href="{{ route('orders.success', $order) }}" class="btn-outline btn-sm">Lihat invoice</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.store>

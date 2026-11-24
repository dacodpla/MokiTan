@php
    $filters = [
        ['label' => 'Semua', 'status' => null],
        ['label' => 'Pending', 'status' => \App\Enums\OrderStatus::Pending],
        ['label' => 'Paid', 'status' => \App\Enums\OrderStatus::Paid],
        ['label' => 'Shipped', 'status' => \App\Enums\OrderStatus::Shipped],
    ];
@endphp

<x-layouts.admin title="Transactions">
    <x-store.breadcrumb :items="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Transactions'],
    ]" />

    <h1 class="type-h1 mb-6">Transactions</h1>

    @if ($errors->any())
        <div class="mb-6 shape-card border border-brand bg-brand/5 p-4 type-body text-brand" role="alert">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="shape-card border border-ink/10 p-5">
            <div class="type-body text-ink/60">Total Produk</div>
            <div class="type-h2 mt-1">{{ $totalProducts }}</div>
        </div>
        <div class="shape-card border border-ink/10 p-5">
            <div class="type-body text-ink/60">Pesanan Pending</div>
            <div class="type-h2 mt-1">{{ $pendingCount }}</div>
        </div>
        <div class="shape-card border border-ink/10 p-5">
            <div class="type-body text-ink/60">Pesanan Paid + Shipped</div>
            <div class="type-h2 mt-1">{{ $fulfilledCount }}</div>
        </div>
        <div class="shape-card border border-ink/10 p-5">
            <div class="type-body text-ink/60">Total Omzet Bersih</div>
            <div class="type-h2 mt-1 text-2xl lg:text-[36px]">{{ \App\Support\Money::idr($netRevenue) }}</div>
            <div class="type-body text-xs text-ink/50 mt-1">Hanya pesanan Paid &amp; Shipped</div>
        </div>
    </div>

    <nav class="mb-6 flex flex-wrap gap-2" aria-label="Filter status pesanan">
        @foreach ($filters as $filter)
            @php $isActive = $activeStatus === $filter['status']; @endphp
            <a href="{{ route('admin.transactions.index', $filter['status'] ? ['status' => $filter['status']->value] : []) }}"
               @if ($isActive) aria-current="page" @endif
               class="btn-sm shape-card px-4 border {{ $isActive ? 'bg-ink text-canvas border-ink' : 'border-ink/30 hover:border-ink' }} type-body font-medium transition">
                {{ $filter['label'] }}
            </a>
        @endforeach
    </nav>

    @if ($transactions->isEmpty())
        <div class="shape-card border border-ink/10 p-14 text-center">
            <div class="type-h3 mb-2">Belum ada pesanan</div>
            <p class="type-body text-ink/60">
                {{ $activeStatus ? 'Tidak ada pesanan dengan status '.$activeStatus->label().'.' : 'Pesanan akan muncul di sini setelah pembeli checkout.' }}
            </p>
        </div>
    @else
        <div class="shape-card border border-ink/10 overflow-x-auto">
            <table class="w-full min-w-[960px] text-left type-body text-sm">
                <thead class="bg-surface/50">
                    <tr>
                        <th class="p-3">Invoice</th>
                        <th class="p-3">Pembeli</th>
                        <th class="p-3">Barang &amp; Kurir</th>
                        <th class="p-3 text-right">Total</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Ubah status &amp; resi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $order)
                        @php $ship = $order->shipping_snapshot ?? []; @endphp
                        <tr class="border-t border-ink/10 align-top">
                            <td class="p-3">
                                <div class="font-bold">{{ $order->order_number }}</div>
                                <div class="text-ink/60 text-xs">{{ $order->created_at->format('d M Y, H:i') }}</div>
                                <a href="{{ route('orders.success', $order) }}" target="_blank"
                                   class="text-xs text-brand hover:underline">Lihat invoice ↗</a>
                            </td>
                            <td class="p-3">
                                <div class="font-medium">{{ $ship['recipient_name'] ?? $order->user?->name ?? 'Guest' }}</div>
                                <div class="text-ink/60 text-xs">{{ $ship['phone'] ?? '' }}</div>
                                <div class="text-ink/60 text-xs max-w-[200px]">
                                    {{ $ship['line1'] ?? '' }}, {{ $ship['city'] ?? '' }}
                                </div>
                            </td>
                            <td class="p-3">
                                <ul>
                                    @foreach ($order->items as $item)
                                        <li>{{ $item->product_snapshot['name'] }} × {{ $item->quantity }}</li>
                                    @endforeach
                                </ul>
                                <div class="text-xs text-ink/60 mt-1">
                                    {{ $order->courier?->value ?? '—' }} (ongkir {{ \App\Support\Money::idr($order->shipping_fee) }})
                                </div>
                            </td>
                            <td class="p-3 text-right font-bold whitespace-nowrap">{{ \App\Support\Money::idr($order->total) }}</td>
                            <td class="p-3">
                                <x-store.status-badge :status="$order->status" />
                                @if ($order->tracking_number)
                                    <div class="text-xs text-ink/60 mt-1">Resi: {{ $order->tracking_number }}</div>
                                @endif
                            </td>
                            <td class="p-3">
                                <form method="POST" action="{{ route('admin.transactions.status', $order->id) }}"
                                      class="flex flex-col gap-2">
                                    @csrf
                                    <select name="status" aria-label="Status" class="input-underline py-1">
                                        @foreach (\App\Enums\OrderStatus::cases() as $case)
                                            <option value="{{ $case->value }}" @selected($order->status === $case)>
                                                {{ $case->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="tracking_number" value="{{ $order->tracking_number }}"
                                           placeholder="No. resi (wajib jika Shipped)" aria-label="Nomor resi"
                                           class="input-underline py-1 text-sm">
                                    <button type="submit" class="btn-success btn-sm">Simpan</button>
                                </form>

                                <form method="POST" action="{{ route('admin.transactions.destroy', $order->id) }}"
                                      class="mt-2" onsubmit="return confirm('Hapus invoice {{ $order->order_number }}? Tindakan ini tidak bisa dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm w-full">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $transactions->links() }}</div>
    @endif
</x-layouts.admin>

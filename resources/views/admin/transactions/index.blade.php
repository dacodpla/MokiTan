<x-layouts.admin title="Transactions">
    <x-store.breadcrumb :items="[
        ['label' => 'Admin', 'url' => route('admin.dashboard')],
        ['label' => 'Transactions'],
    ]" />

    <h1 class="type-h1 mb-6">Transactions</h1>

    <div class="grid gap-4 sm:grid-cols-3 mb-8">
        <div class="shape-card border border-ink/10 p-5">
            <div class="type-body text-ink/60">Total orders</div>
            <div class="type-h2 mt-1">{{ $count }}</div>
        </div>
        <div class="shape-card border border-ink/10 p-5">
            <div class="type-body text-ink/60">Revenue (paid)</div>
            <div class="type-h2 mt-1">{{ \App\Support\Money::idr($revenue) }}</div>
        </div>
        <div class="shape-card border border-ink/10 p-5">
            <div class="type-body text-ink/60">Latest order</div>
            @if ($latest)
                <div class="type-h5 mt-1">{{ $latest->order_number }}</div>
                <div class="type-body text-ink/50 text-sm">{{ $latest->created_at->diffForHumans() }}</div>
            @else
                <div class="type-h5 mt-1 text-ink/40">None yet</div>
            @endif
        </div>
    </div>

    @if ($transactions->isEmpty())
        <div class="shape-card border border-ink/10 p-14 text-center">
            <div class="type-h3 mb-2">No transactions yet</div>
            <p class="type-body text-ink/60">Orders will show up here once customers check out.</p>
        </div>
    @else
        <div class="shape-card border border-ink/10 overflow-hidden">
            <table class="w-full text-left type-body">
                <thead class="bg-surface/50">
                    <tr>
                        <th class="p-3">Order</th>
                        <th class="p-3">Customer</th>
                        <th class="p-3">Date</th>
                        <th class="p-3 text-right">Total</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Update status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $order)
                        <tr class="border-t border-ink/10">
                            <td class="p-3 font-medium">{{ $order->order_number }}</td>
                            <td class="p-3">{{ $order->user?->name ?? 'Guest' }}</td>
                            <td class="p-3 text-ink/60">{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td class="p-3 text-right font-bold">{{ \App\Support\Money::idr($order->total) }}</td>
                            <td class="p-3">
                                <x-store.status-badge :status="$order->status" />
                            </td>
                            <td class="p-3">
                                <form method="POST" action="{{ route('admin.transactions.status', $order->id) }}"
                                      class="flex items-center gap-2">
                                    @csrf
                                    <select name="status" class="input-underline py-1">
                                        @foreach (\App\Enums\OrderStatus::cases() as $case)
                                            <option value="{{ $case->value }}" @selected($order->status === $case)>
                                                {{ $case->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn-outline py-1 px-4 text-sm">Save</button>
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

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $activeStatus = OrderStatus::tryFrom((string) $request->query('status'));

        $transactions = Order::with(['user', 'items'])
            ->when($activeStatus, fn ($query) => $query->where('status', $activeStatus))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.transactions.index', [
            'transactions' => $transactions,
            'activeStatus' => $activeStatus,
            'totalProducts' => Product::count(),
            'pendingCount' => Order::where('status', OrderStatus::Pending)->count(),
            'fulfilledCount' => Order::whereIn('status', OrderStatus::revenueValues())->count(),
            'netRevenue' => (int) Order::whereIn('status', OrderStatus::revenueValues())->sum('total'),
        ]);
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'tracking_number' => ['nullable', 'string', 'max:60', 'required_if:status,'.OrderStatus::Shipped->value],
        ]);

        $order = Order::findOrFail($id);
        $status = OrderStatus::from($validated['status']);

        $order->update([
            'status' => $status,
            // A tracking number only makes sense once the parcel has shipped.
            'tracking_number' => $status === OrderStatus::Shipped ? $validated['tracking_number'] : null,
        ]);

        return back()->with('status', "Invoice {$order->order_number} is now {$status->label()}.");
    }

    /**
     * Deleting a Pending order cancels it, so its reserved stock goes back on the shelf.
     * Paid/Shipped orders were fulfilled, so their stock is not restored.
     */
    public function destroy(int $id): RedirectResponse
    {
        $order = Order::with('items')->findOrFail($id);

        DB::transaction(function () use ($order): void {
            if ($order->status === OrderStatus::Pending) {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        Product::whereKey($item->product_id)->increment('stock', $item->quantity);
                    }
                }
            }

            $order->delete();
        });

        return redirect()
            ->route('admin.transactions.index')
            ->with('status', "Invoice {$order->order_number} was deleted.");
    }
}

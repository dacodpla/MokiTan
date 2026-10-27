<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(): View
    {
        return view('admin.transactions.index', [
            'transactions' => Order::with('user')->latest()->paginate(20),
            'latest' => Order::latest()->first(),
            'count' => Order::count(),
            'revenue' => Order::where('status', OrderStatus::Paid)->sum('total'),
        ]);
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_column(OrderStatus::cases(), 'value'))],
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $validated['status']]);

        return redirect()
            ->route('admin.transactions.index')
            ->with('status', "Order {$order->order_number} marked as ".OrderStatus::from($validated['status'])->label().'.');
    }
}

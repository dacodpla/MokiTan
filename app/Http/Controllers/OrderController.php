<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * "Pesanan Saya": only the signed-in customer's own orders, newest first.
     */
    public function index(Request $request): View
    {
        return view('orders.index', [
            'orders' => $request->user()->orders()->with('items')->latest()->get(),
        ]);
    }

    public function success(Request $request, Order $order): View
    {
        $this->authorizeOwner($request, $order);

        return view('orders.success', ['order' => $order->load('items')]);
    }

    public function track(Request $request, Order $order): View
    {
        $this->authorizeOwner($request, $order);

        return view('orders.track', ['order' => $order->load('items')]);
    }

    /**
     * An invoice holds a name, phone number and address, so only its owner
     * (or an admin) may open it.
     */
    private function authorizeOwner(Request $request, Order $order): void
    {
        abort_unless(
            $order->user_id === $request->user()->id || $request->user()->isAdmin(),
            403,
            'Anda tidak berhak melihat invoice ini.',
        );
    }
}

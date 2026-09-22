<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;

class OrderController extends Controller
{
    public function success(Order $order): View
    {
        $order->load('items');

        return view('orders.success', ['order' => $order]);
    }

    public function track(Order $order): View
    {
        $order->load('items');

        return view('orders.track', ['order' => $order]);
    }
}

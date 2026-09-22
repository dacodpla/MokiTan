<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $cart = Cart::current();
        if ($cart->items()->count() === 0) {
            return redirect()->route('catalog');
        }

        return view('checkout.index');
    }
}

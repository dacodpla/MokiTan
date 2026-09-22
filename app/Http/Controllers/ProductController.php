<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        $product->load(['category', 'recommendations']);

        return view('products.show', [
            'product' => $product,
        ]);
    }
}

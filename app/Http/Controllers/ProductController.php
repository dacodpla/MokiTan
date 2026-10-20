<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function show(int $id): View
    {
        $product = Product::with(['category', 'recommendations'])->findOrFail($id);

        return view('products.show', [
            'product' => $product,
        ]);
    }
}

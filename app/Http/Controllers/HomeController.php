<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'featured' => Product::where('is_featured', true)->latest()->first(),
            'categories' => Category::orderBy('sort_order')->get(),
            'newInStock' => Product::latest()->take(8)->get(),
        ]);
    }
}

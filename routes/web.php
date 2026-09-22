<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/catalog', fn () => view('catalog.index'))->name('catalog');
Route::get('/catalog/{category:slug}', fn (Category $category) => view('catalog.index', ['categorySlug' => $category->slug]))
    ->name('catalog.category');

Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');

Route::get('/orders/{order}/success', [OrderController::class, 'success'])->name('orders.success');
Route::get('/orders/{order}/track', [OrderController::class, 'track'])->name('orders.track');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', fn () => redirect()->route('admin.products.index'))->name('dashboard');
        Route::resource('products', App\Http\Controllers\Admin\ProductController::class)
            ->except('show');
    });
});

require __DIR__.'/auth.php';

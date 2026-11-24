<?php

use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;

// Public storefront
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/catalog', fn () => view('catalog.index'))->name('catalog');
Route::get('/catalog/{category:slug}', fn (Category $category) => view('catalog.index', ['categorySlug' => $category->slug]))
    ->name('catalog.category');

Route::get('/detail/{id}', [ProductController::class, 'show'])->whereNumber('id')->name('products.show');

// Logged-in users (customer or admin)
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');

    Route::get('/pesanan-saya', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}/success', [OrderController::class, 'success'])->name('orders.success');
    Route::get('/orders/{order}/track', [OrderController::class, 'track'])->name('orders.track');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Seller Center: admin role only (guests are sent to /login by `auth`, customers get a 403 from `admin`)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.transactions.index'))->name('dashboard');

    Route::resource('products', AdminProductController::class)->except('show');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions/{id}/status', [TransactionController::class, 'updateStatus'])
        ->whereNumber('id')
        ->name('transactions.status');
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])
        ->whereNumber('id')
        ->name('transactions.destroy');
});

require __DIR__.'/auth.php';

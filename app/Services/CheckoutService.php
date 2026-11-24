<?php

namespace App\Services;

use App\Enums\Courier;
use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /**
     * Turn the user's cart into a Pending order and take the stock.
     *
     * Stock is re-checked against locked product rows inside the transaction,
     * so a stale cart (or a tampered quantity) can never oversell a product.
     *
     * @param  array<string, string|null>  $shipping
     *
     * @throws ValidationException when the cart is empty or a product lacks stock
     */
    public function placeOrder(User $user, Cart $cart, array $shipping, Courier $courier): Order
    {
        return DB::transaction(function () use ($user, $cart, $shipping, $courier): Order {
            $items = $cart->items()->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }

            $wanted = $items->groupBy('product_id')
                ->map(fn ($group): int => (int) $group->sum('quantity'));

            $products = Product::whereIn('id', $wanted->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($wanted as $productId => $quantity) {
                $product = $products[$productId];

                if ($quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'stock' => "Only {$product->stock} unit(s) of {$product->name} left, but you have {$quantity} in your cart.",
                    ]);
                }
            }

            $subtotal = (int) $items->sum(fn ($item): int => $products[$item->product_id]->price * $item->quantity);
            $shippingFee = $courier->fee();

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $this->newInvoiceNumber(),
                'status' => OrderStatus::Pending,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'courier' => $courier,
                'total' => $subtotal + $shippingFee,
                'shipping_snapshot' => $shipping,
            ]);

            foreach ($items as $item) {
                $product = $products[$item->product_id];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_snapshot' => [
                        'name' => $product->name,
                        'brand' => $product->brand,
                        'price' => $product->price,
                        'image_path' => $product->image_path,
                        'bundle_meta' => $item->bundle_meta,
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => $product->price,
                    'is_gift' => $item->is_gift,
                    'gift_note' => $item->gift_note,
                ]);
            }

            foreach ($wanted as $productId => $quantity) {
                $products[$productId]->decrement('stock', $quantity);
            }

            $cart->items()->delete();

            return $order;
        });
    }

    private function newInvoiceNumber(): string
    {
        return 'INV-'.now()->format('Y').'-'.Str::upper(Str::random(6));
    }
}

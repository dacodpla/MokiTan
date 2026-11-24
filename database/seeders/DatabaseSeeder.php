<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Safe to re-run: everything is keyed by email/slug, nothing is deleted.
     * Use `php artisan db:seed` — never `migrate:fresh` — once real data exists.
     */
    public function run(): void
    {
        $this->seedAccounts();
        $this->seedCategories();
        $this->seedDemoOrder();
    }

    private function seedAccounts(): void
    {
        // role is not mass-assignable on purpose, so it is set with forceFill().
        $accounts = [
            ['Admin Moki-Tan', 'admin@mokitan.com', 'admin123', 'admin'],
            ['Budi Santoso', 'budi@mokitan.com', 'customer123', 'customer'],
            ['Siti Rahma', 'siti@mokitan.com', 'customer123', 'customer'],
        ];

        foreach ($accounts as [$name, $email, $password, $role]) {
            User::firstOrNew(['email' => $email])
                ->forceFill([
                    'name' => $name,
                    'password' => $password,
                    'role' => $role,
                    'email_verified_at' => now(),
                ])
                ->save();
        }
    }

    private function seedCategories(): void
    {
        $categories = [
            ['Kits', 'kits', 1],
            ['Tools', 'tools', 2],
            ['Materials', 'materials', 3],
            ['Paints', 'paints', 4],
            ['Other', 'other', 5],
        ];

        foreach ($categories as [$name, $slug, $sortOrder]) {
            Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'sort_order' => $sortOrder],
            );
        }
    }

    /**
     * Gives customer 2 one order, so ownership checks can be demonstrated
     * (customer 1 must get a 403 on this invoice). Needs at least one product.
     */
    private function seedDemoOrder(): void
    {
        $product = Product::first();
        $siti = User::where('email', 'siti@mokitan.com')->first();

        if (! $product || ! $siti || $siti->orders()->exists()) {
            return;
        }

        $order = Order::factory()->for($siti)->create([
            'subtotal' => $product->price,
            'total' => $product->price + 20000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_snapshot' => [
                'name' => $product->name,
                'brand' => $product->brand,
                'price' => $product->price,
                'image_path' => $product->image_path,
                'bundle_meta' => null,
            ],
            'quantity' => 1,
            'unit_price' => $product->price,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Enums\Courier;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'INV-'.now()->format('Y').'-'.Str::upper(Str::random(6)),
            'status' => OrderStatus::Pending,
            'subtotal' => 100000,
            'shipping_fee' => Courier::JneReguler->fee(),
            'courier' => Courier::JneReguler,
            'total' => 100000 + Courier::JneReguler->fee(),
            'shipping_snapshot' => [
                'recipient_name' => fake()->name(),
                'phone' => '081200000000',
                'line1' => 'Jl. Contoh No. 1',
                'city' => 'Bogor',
                'province' => 'Jawa Barat',
                'postal_code' => '16911',
            ],
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => OrderStatus::Paid]);
    }

    public function shipped(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::Shipped,
            'tracking_number' => 'RESI'.fake()->numerify('########'),
        ]);
    }
}

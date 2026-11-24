<?php

namespace Tests\Feature;

use App\Enums\Courier;
use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function shipping(): array
    {
        return [
            'recipient_name' => 'Budi Santoso',
            'phone' => '081200000000',
            'line1' => 'Jl. Contoh No. 1',
            'line2' => '',
            'city' => 'Bogor',
            'province' => 'Jawa Barat',
            'postal_code' => '16911',
        ];
    }

    private function cartWith(User $user, Product $product, int $quantity): Cart
    {
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => $quantity]);

        return $cart;
    }

    public function test_placing_an_order_creates_a_pending_order_and_takes_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 100000]);
        $cart = $this->cartWith($user, $product, 3);

        $order = app(CheckoutService::class)->placeOrder($user, $cart, $this->shipping(), Courier::JntExpress);

        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(300000, $order->subtotal);
        $this->assertSame(18000, $order->shipping_fee);
        $this->assertSame(318000, $order->total);
        $this->assertSame(Courier::JntExpress, $order->courier);
        $this->assertCount(1, $order->items);
        $this->assertSame(7, $product->fresh()->stock);
        $this->assertSame(0, $cart->items()->count());
    }

    public function test_quantity_above_stock_is_rejected_and_nothing_changes(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 2]);
        $cart = $this->cartWith($user, $product, 5);

        try {
            app(CheckoutService::class)->placeOrder($user, $cart, $this->shipping(), Courier::JneReguler);
            $this->fail('Expected a ValidationException for insufficient stock.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('stock', $e->errors());
        }

        $this->assertSame(0, Order::count());
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame(1, $cart->items()->count());
    }

    public function test_split_cart_rows_of_one_product_are_checked_together(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 3]);
        $cart = $this->cartWith($user, $product, 2);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'bundle_meta' => ['label' => 'Quick Added'],
        ]);

        $this->expectException(ValidationException::class);

        app(CheckoutService::class)->placeOrder($user, $cart, $this->shipping(), Courier::JneReguler);
    }

    public function test_an_empty_cart_cannot_be_checked_out(): void
    {
        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id]);

        $this->expectException(ValidationException::class);

        app(CheckoutService::class)->placeOrder($user, $cart, $this->shipping(), Courier::JneReguler);
    }

    public function test_checkout_form_places_a_pending_order_and_redirects_to_the_invoice(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 4, 'price' => 50000]);
        $this->cartWith($user, $product, 2);

        Livewire::actingAs($user)
            ->test('checkout.personal-data')
            ->set('recipient_name', 'Budi Santoso')
            ->set('phone', '081200000000')
            ->set('line1', 'Jl. Contoh No. 1')
            ->set('city', 'Bogor')
            ->set('province', 'Jawa Barat')
            ->set('postal_code', '16911')
            ->set('courier', Courier::SicepatBest->value)
            ->call('placeOrder')
            ->assertHasNoErrors()
            ->assertRedirect(route('orders.success', Order::first()));

        $this->assertSame(OrderStatus::Pending, Order::first()->status);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_checkout_form_shows_a_stock_error_and_keeps_the_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 1]);
        $cart = $this->cartWith($user, $product, 4);

        Livewire::actingAs($user)
            ->test('checkout.personal-data')
            ->set('recipient_name', 'Budi Santoso')
            ->set('phone', '081200000000')
            ->set('line1', 'Jl. Contoh No. 1')
            ->set('city', 'Bogor')
            ->set('province', 'Jawa Barat')
            ->set('postal_code', '16911')
            ->call('placeOrder')
            ->assertHasErrors(['stock'])
            ->assertSet('recipient_name', 'Budi Santoso');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $cart->items()->count());
    }

    public function test_checkout_form_requires_the_shipping_fields(): void
    {
        $user = User::factory()->create();
        $this->cartWith($user, Product::factory()->create(), 1);

        Livewire::actingAs($user)
            ->test('checkout.personal-data')
            ->set('recipient_name', '')
            ->call('placeOrder')
            ->assertHasErrors(['recipient_name', 'phone', 'line1', 'city']);
    }

    public function test_guests_are_sent_to_login_when_adding_to_cart(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        Livewire::test('product.add-to-cart', ['product' => $product])
            ->call('add')
            ->assertRedirect(route('login'));

        $this->assertSame(0, CartItem::count());
    }

    public function test_add_to_cart_cannot_exceed_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 3]);

        Livewire::actingAs($user)
            ->test('product.add-to-cart', ['product' => $product])
            ->set('quantity', 5)
            ->call('add')
            ->assertHasErrors(['quantity']);

        $this->assertSame(0, CartItem::count());
    }

    public function test_add_to_cart_counts_what_is_already_in_the_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 3]);
        $this->cartWith($user, $product, 2);

        Livewire::actingAs($user)
            ->test('product.add-to-cart', ['product' => $product])
            ->set('quantity', 2)
            ->call('add')
            ->assertHasErrors(['quantity']);

        $this->assertSame(2, CartItem::first()->quantity);
    }

    public function test_add_to_cart_works_within_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 3]);

        Livewire::actingAs($user)
            ->test('product.add-to-cart', ['product' => $product])
            ->set('quantity', 2)
            ->call('add')
            ->assertHasNoErrors();

        $this->assertSame(2, CartItem::first()->quantity);
    }

    public function test_a_sold_out_product_shows_stok_habis_and_cannot_be_added(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 0]);

        Livewire::actingAs($user)
            ->test('product.add-to-cart', ['product' => $product])
            ->assertSee('Stok Habis')
            ->call('add')
            ->assertHasErrors(['quantity']);

        $this->assertSame(0, CartItem::count());
    }
}

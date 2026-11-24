<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    private function orderFor(Product $product, int $quantity, OrderStatus $status): Order
    {
        $order = Order::factory()->create(['status' => $status]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_snapshot' => ['name' => $product->name, 'brand' => $product->brand, 'price' => $product->price],
            'quantity' => $quantity,
            'unit_price' => $product->price,
        ]);

        return $order;
    }

    public function test_net_revenue_only_counts_paid_and_shipped_orders(): void
    {
        Order::factory()->create(['status' => OrderStatus::Pending, 'total' => 100000]);
        Order::factory()->paid()->create(['total' => 200000]);
        Order::factory()->shipped()->create(['total' => 300000]);

        $this->actingAs($this->admin)
            ->get(route('admin.transactions.index'))
            ->assertOk()
            ->assertSee('Rp 500.000')
            ->assertDontSee('Rp 600.000');
    }

    public function test_dashboard_counts_products_and_orders(): void
    {
        Product::factory()->count(3)->create();
        Order::factory()->count(2)->create(['status' => OrderStatus::Pending]);
        Order::factory()->paid()->create();
        Order::factory()->shipped()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.transactions.index'));

        $response->assertViewHas('totalProducts', 3);
        $response->assertViewHas('pendingCount', 2);
        $response->assertViewHas('fulfilledCount', 2);
    }

    public function test_status_filter_only_lists_matching_orders(): void
    {
        $pending = Order::factory()->create(['status' => OrderStatus::Pending]);
        $paid = Order::factory()->paid()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.transactions.index', ['status' => 'paid']))
            ->assertSee($paid->order_number)
            ->assertDontSee($pending->order_number);
    }

    public function test_shipping_an_order_requires_a_tracking_number(): void
    {
        $order = Order::factory()->paid()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.transactions.index'))
            ->post(route('admin.transactions.status', $order->id), ['status' => 'shipped'])
            ->assertSessionHasErrors('tracking_number');

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
    }

    public function test_shipping_with_a_tracking_number_saves_it_and_going_back_clears_it(): void
    {
        $order = Order::factory()->paid()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.transactions.status', $order->id), ['status' => 'shipped', 'tracking_number' => 'JNE12345'])
            ->assertSessionHasNoErrors();

        $this->assertSame(OrderStatus::Shipped, $order->fresh()->status);
        $this->assertSame('JNE12345', $order->fresh()->tracking_number);

        $this->actingAs($this->admin)
            ->post(route('admin.transactions.status', $order->id), ['status' => 'paid', 'tracking_number' => 'JNE12345']);

        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertNull($order->fresh()->tracking_number);
    }

    public function test_an_unknown_status_is_rejected(): void
    {
        $order = Order::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.transactions.status', $order->id), ['status' => 'refunded'])
            ->assertSessionHasErrors('status');
    }

    public function test_customers_cannot_change_or_delete_orders(): void
    {
        $customer = User::factory()->create();
        $order = Order::factory()->create();

        $this->actingAs($customer)
            ->post(route('admin.transactions.status', $order->id), ['status' => 'paid'])
            ->assertForbidden();

        $this->actingAs($customer)
            ->delete(route('admin.transactions.destroy', $order->id))
            ->assertForbidden();

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_deleting_a_pending_order_puts_its_stock_back(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $order = $this->orderFor($product, 2, OrderStatus::Pending);

        $this->actingAs($this->admin)
            ->delete(route('admin.transactions.destroy', $order->id))
            ->assertRedirect(route('admin.transactions.index'));

        $this->assertModelMissing($order);
        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_deleting_a_paid_order_does_not_restore_stock(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $order = $this->orderFor($product, 2, OrderStatus::Paid);

        $this->actingAs($this->admin)->delete(route('admin.transactions.destroy', $order->id));

        $this->assertModelMissing($order);
        $this->assertSame(5, $product->fresh()->stock);
    }
}

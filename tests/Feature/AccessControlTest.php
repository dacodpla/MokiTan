<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login_for_protected_pages(): void
    {
        foreach (['/admin/transactions', '/admin/products', '/cart', '/checkout', '/pesanan-saya'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_customers_get_403_on_every_admin_page(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin/transactions')->assertForbidden();
        $this->actingAs($customer)->get('/admin/products')->assertForbidden();
        $this->actingAs($customer)->get('/admin/products/create')->assertForbidden();
    }

    public function test_admin_can_open_the_seller_center(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/transactions')->assertOk();
        $this->actingAs($admin)->get('/admin/products')->assertOk();
    }

    public function test_customer_cannot_open_another_customers_invoice(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = Order::factory()->for($owner)->create();

        $this->actingAs($intruder)->get(route('orders.success', $order))->assertForbidden();
        $this->actingAs($intruder)->get(route('orders.track', $order))->assertForbidden();
    }

    public function test_owner_and_admin_can_open_an_invoice(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->for($owner)->create();

        $this->actingAs($owner)->get(route('orders.success', $order))->assertOk();
        $this->actingAs($admin)->get(route('orders.success', $order))->assertOk();
    }

    public function test_pesanan_saya_only_lists_my_own_orders(): void
    {
        $me = User::factory()->create();
        $someoneElse = User::factory()->create();
        $mine = Order::factory()->for($me)->create();
        $theirs = Order::factory()->for($someoneElse)->create();

        $this->actingAs($me)->get('/pesanan-saya')
            ->assertOk()
            ->assertSee($mine->order_number)
            ->assertDontSee($theirs->order_number);
    }

    public function test_pesanan_saya_shows_an_empty_state(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/pesanan-saya')
            ->assertOk()
            ->assertSee('Belum ada pesanan');
    }

    public function test_a_fake_product_id_returns_404(): void
    {
        $this->get('/detail/999')->assertNotFound();
    }

    public function test_registration_always_creates_a_customer(): void
    {
        $this->post('/register', [
            'name' => 'Siswa Baru',
            'email' => 'baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertSame('customer', User::where('email', 'baru@example.com')->value('role'));
    }

    public function test_login_redirects_by_role(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.transactions.index', absolute: false));

        $this->post('/logout');

        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('home', absolute: false));
    }

    public function test_logout_requires_a_post_request(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/logout')
            ->assertStatus(405);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuickAddTest extends TestCase
{
    use RefreshDatabase;

    private Product $kit;

    private Product $cement;

    private Product $putty;

    private Product $paint;

    private Product $tool;

    private Product $accessory;

    protected function setUp(): void
    {
        parent::setUp();

        $category = fn (string $slug) => Category::factory()->create(['name' => ucfirst($slug), 'slug' => $slug]);

        $this->kit = Product::factory()->create(['category_id' => $category('kits')->id, 'stock' => 5]);
        $this->cement = Product::factory()->create(['category_id' => $category('materials')->id, 'stock' => 5]);
        $this->putty = Product::factory()->create(['category_id' => Category::where('slug', 'materials')->first()->id, 'stock' => 0]);
        $this->paint = Product::factory()->create(['category_id' => $category('paints')->id, 'stock' => 5]);
        $this->tool = Product::factory()->create(['category_id' => $category('tools')->id, 'stock' => 5]);

        $this->accessory = Product::factory()->create(['category_id' => $category('other')->id, 'stock' => 5]);

        $this->kit->recommendations()->sync([
            $this->cement->id, $this->putty->id, $this->paint->id, $this->tool->id, $this->accessory->id,
        ]);
    }

    /**
     * @return array<int, int> product id => quantity in the cart
     */
    private function cartContents(User $user): array
    {
        return Cart::where('user_id', $user->id)->first()
            ->items->groupBy('product_id')->map->sum('quantity')->all();
    }

    public function test_starter_option_adds_the_kit_and_every_linked_item_except_accessories(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('product.quick-add', ['product' => $this->kit])
            ->call('select', 'starter')
            ->call('addBundle')
            ->assertHasNoErrors();

        $this->assertEquals([
            $this->kit->id => 1,
            $this->cement->id => 1,
            $this->paint->id => 1,
            $this->tool->id => 1,
        ], $this->cartContents($user));
    }

    public function test_restock_option_adds_the_kit_and_only_materials(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('product.quick-add', ['product' => $this->kit])
            ->call('select', 'restock')
            ->call('addBundle')
            ->assertHasNoErrors();

        $this->assertEquals([$this->kit->id => 1, $this->cement->id => 1], $this->cartContents($user));
    }

    public function test_paints_option_adds_the_kit_and_only_paints(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('product.quick-add', ['product' => $this->kit])
            ->call('select', 'paints')
            ->call('addBundle')
            ->assertHasNoErrors();

        $this->assertEquals([$this->kit->id => 1, $this->paint->id => 1], $this->cartContents($user));
    }

    public function test_adding_without_choosing_an_option_is_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('product.quick-add', ['product' => $this->kit])
            ->call('addBundle')
            ->assertHasErrors('bundle');

        $this->assertSame(0, Cart::where('user_id', $user->id)->count());
    }

    public function test_sold_out_kit_adds_nothing(): void
    {
        $this->kit->update(['stock' => 0]);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('product.quick-add', ['product' => $this->kit])
            ->call('select', 'restock')
            ->call('addBundle')
            ->assertHasErrors('bundle');

        $this->assertSame(0, Cart::where('user_id', $user->id)->first()?->items()->count() ?? 0);
    }

    public function test_extras_never_exceed_stock_across_repeated_quick_adds(): void
    {
        $this->cement->update(['stock' => 1]);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('product.quick-add', ['product' => $this->kit])
            ->call('select', 'restock')
            ->call('addBundle')
            ->assertHasNoErrors()
            ->call('select', 'restock')
            ->call('addBundle')
            ->assertHasNoErrors();

        $this->assertEquals([$this->kit->id => 2, $this->cement->id => 1], $this->cartContents($user));
    }

    public function test_guests_are_sent_to_login(): void
    {
        Livewire::test('product.quick-add', ['product' => $this->kit])
            ->call('select', 'starter')
            ->call('addBundle')
            ->assertRedirect(route('login'));
    }

    public function test_unknown_option_values_are_ignored(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('product.quick-add', ['product' => $this->kit])
            ->call('select', 'everything')
            ->assertSet('option', null);
    }

    public function test_accessories_are_never_part_of_a_preset(): void
    {
        $user = User::factory()->create();

        foreach (['starter', 'restock', 'paints'] as $option) {
            Livewire::actingAs($user)
                ->test('product.quick-add', ['product' => $this->kit])
                ->call('select', $option)
                ->call('addBundle');
        }

        $this->assertArrayNotHasKey($this->accessory->id, $this->cartContents($user));
    }

    public function test_picked_accessories_are_added_on_top_of_the_chosen_option(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('product.quick-add', ['product' => $this->kit])
            ->call('select', 'paints')
            ->call('toggleAccessory', $this->accessory->id)
            ->call('addBundle')
            ->assertHasNoErrors()
            ->assertSet('accessories', []);

        $this->assertEquals([
            $this->kit->id => 1,
            $this->paint->id => 1,
            $this->accessory->id => 1,
        ], $this->cartContents($user));
    }

    public function test_only_linked_accessories_can_be_added(): void
    {
        $unlinked = Product::factory()->create([
            'category_id' => $this->accessory->category_id,
            'stock' => 5,
        ]);
        $nonAccessory = $this->tool;
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('product.quick-add', ['product' => $this->kit])
            ->call('select', 'paints')
            ->call('toggleAccessory', $unlinked->id)
            ->call('toggleAccessory', $nonAccessory->id)
            ->call('addBundle');

        $this->assertEquals([$this->kit->id => 1, $this->paint->id => 1], $this->cartContents($user));
    }

    public function test_sold_out_accessories_are_skipped(): void
    {
        $this->accessory->update(['stock' => 0]);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('product.quick-add', ['product' => $this->kit])
            ->call('select', 'paints')
            ->call('toggleAccessory', $this->accessory->id)
            ->call('addBundle')
            ->assertHasNoErrors();

        $this->assertEquals([$this->kit->id => 1, $this->paint->id => 1], $this->cartContents($user));
    }

    public function test_quick_added_items_live_inside_the_kits_cart_line(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('product.quick-add', ['product' => $this->kit])
            ->call('select', 'paints')
            ->call('toggleAccessory', $this->accessory->id)
            ->call('addBundle');

        $lines = Cart::where('user_id', $user->id)->first()->lines()->with('children')->get();

        $this->assertCount(1, $lines);
        $this->assertSame($this->kit->id, $lines->first()->product_id);
        $this->assertEqualsCanonicalizing(
            [$this->paint->id, $this->accessory->id],
            $lines->first()->children->pluck('product_id')->all(),
        );
    }

    public function test_cart_page_removes_bundles_whole_and_children_individually(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('product.quick-add', ['product' => $this->kit])
            ->call('select', 'paints')
            ->call('addBundle');

        $bundle = Cart::where('user_id', $user->id)->first()->lines()->first();
        $child = $bundle->children()->first();

        Livewire::actingAs($user)
            ->test('cart.page')
            ->assertSee('Show')
            ->assertSee($this->paint->name)
            ->call('increment', $bundle->id)
            ->call('remove', $child->id);

        $this->assertSame(1, $bundle->fresh()->quantity);
        $this->assertSame(0, $bundle->children()->count());

        Livewire::actingAs($user)->test('cart.page')->call('remove', $bundle->id);

        $this->assertSame(0, Cart::where('user_id', $user->id)->first()->items()->count());
    }

    public function test_removing_a_bundle_takes_its_children_with_it(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('product.quick-add', ['product' => $this->kit])
            ->call('select', 'starter')
            ->call('addBundle');

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertGreaterThan(1, $cart->items()->count());

        $cart->lines()->first()->delete();

        $this->assertSame(0, $cart->items()->count());
    }
}

<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProductCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_seeded_kit_links_paints_materials_tools_and_accessories(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(ProductCatalogSeeder::class);

        $kits = Product::whereHas('category', fn ($q) => $q->where('slug', 'kits'))->get();

        $this->assertCount(5, $kits);

        foreach ($kits as $kit) {
            $slugs = $kit->recommendations()->with('category')->get()->pluck('category.slug')->unique();

            foreach (['paints', 'materials', 'tools', 'other'] as $slug) {
                $this->assertTrue($slugs->contains($slug), "{$kit->name} has no linked {$slug}.");
            }
        }
    }

    public function test_seeding_twice_changes_nothing_and_keeps_admin_edits(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(ProductCatalogSeeder::class);

        Product::where('slug', 'tamiya-x-2-white')->update(['price' => 99000]);
        $products = Product::count();
        $links = \DB::table('product_recommendations')->count();

        $this->seed(ProductCatalogSeeder::class);

        $this->assertSame($products, Product::count());
        $this->assertSame($links, \DB::table('product_recommendations')->count());
        $this->assertSame(99000, Product::where('slug', 'tamiya-x-2-white')->value('price'));
    }
}

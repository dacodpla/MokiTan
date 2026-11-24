<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Adds starter kits with their linked tools, materials, paints and aftermarket
 * accessories, so Quick Add has something real to work with.
 *
 * Safe to re-run: products are matched by slug and never overwritten, and
 * links are only ever added. Photos are placeholders; swap them in the admin.
 * Run on demand with `php artisan db:seed --class=ProductCatalogSeeder`.
 */
class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');

        foreach ($this->catalog() as $kit) {
            $kitProduct = $this->product($categories, 'kits', $kit['kit']);

            $linked = collect([...$this->sharedSlugs(), ...$kit['link']])
                ->map(fn (string $slug) => Product::where('slug', $slug)->value('id'));

            foreach (['paints', 'accessories'] as $group) {
                foreach ($kit[$group] as $data) {
                    $linked->push($this->product($categories, $group === 'paints' ? 'paints' : 'other', $data)->id);
                }
            }

            $kitProduct->recommendations()->syncWithoutDetaching($linked->filter()->unique()->all());
        }
    }

    /**
     * Tools and materials every kit builder needs. Created once, linked to all kits.
     *
     * @return array<int, string>
     */
    private function sharedSlugs(): array
    {
        $categories = Category::pluck('id', 'slug');

        foreach ($this->essentials() as $category => $items) {
            foreach ($items as $data) {
                $this->product($categories, $category, $data);
            }
        }

        return collect($this->essentials())->flatten(1)
            ->map(fn (array $data) => Str::slug($data['name']))
            ->push('tamiya-extra-thin-cement', 'gh-spn-120')
            ->all();
    }

    /**
     * @param  Collection<string, int>  $categories
     * @param  array<string, mixed>  $data
     */
    private function product($categories, string $category, array $data): Product
    {
        $slug = Str::slug($data['name']);

        return Product::firstOrCreate(['slug' => $slug], [
            'category_id' => $categories[$category],
            'name' => $data['name'],
            'brand' => $data['brand'],
            'price' => $data['price'],
            'description' => $data['description'] ?? null,
            'mold_history' => $data['history'] ?? null,
            'stock' => $data['stock'] ?? 25,
            'specifications' => $data['specs'] ?? null,
            'is_featured' => $data['featured'] ?? false,
            'image_path' => 'img/products/placeholder-'.(crc32($slug) % 6 + 1).'.svg',
        ]);
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function essentials(): array
    {
        return [
            'materials' => [
                ['name' => 'Tamiya Basic Putty', 'brand' => 'TAMIYA', 'price' => 62000, 'stock' => 80,
                    'description' => 'Fills seams, sink marks and gaps. Sand smooth once cured.',
                    'specs' => ['Volume' => '32 g', 'Use' => 'Gap filling']],
                ['name' => 'Tamiya Surface Primer L (Light Gray)', 'brand' => 'TAMIYA', 'price' => 98000, 'stock' => 60,
                    'description' => 'Spray primer that shows up flaws and gives paint something to grip.',
                    'specs' => ['Volume' => '180 ml', 'Type' => 'Spray']],
                ['name' => 'Tamiya Panel Line Accent Color (Black)', 'brand' => 'TAMIYA', 'price' => 46000, 'stock' => 70,
                    'description' => 'Thin wash that flows into panel lines for instant depth.',
                    'specs' => ['Volume' => '40 ml', 'Colour' => 'Black']],
                ['name' => 'Tamiya Masking Tape 10 mm', 'brand' => 'TAMIYA', 'price' => 38000, 'stock' => 90,
                    'description' => 'Low-tack tape for clean camouflage edges and canopy masking.',
                    'specs' => ['Width' => '10 mm', 'Length' => '18 m']],
            ],
            'tools' => [
                ['name' => 'Tamiya Modeler\'s Knife Pro', 'brand' => 'TAMIYA', 'price' => 195000, 'stock' => 40,
                    'description' => 'Balanced hobby knife for trimming gates and cleaning mould lines.'],
                ['name' => 'Tamiya Fine Pointed Tweezers', 'brand' => 'TAMIYA', 'price' => 148000, 'stock' => 40,
                    'description' => 'Fine tips for decals, photo-etch and tiny parts.'],
            ],
        ];
    }

    /**
     * Each kit: the kit itself, extra existing slugs to link, its paints and its aftermarket accessories.
     *
     * @return array<int, array<string, mixed>>
     */
    private function catalog(): array
    {
        $acrylic = fn (string $finish) => ['Volume' => '10 ml', 'Type' => 'Acrylic', 'Finish' => $finish];

        return [
            [
                'kit' => ['name' => 'Tamiya 1/48 Supermarine Spitfire Mk.I', 'brand' => 'TAMIYA', 'price' => 465000, 'stock' => 30, 'featured' => true,
                    'description' => 'Crisp, well-fitting 1/48 Spitfire with a full interior. A great first aircraft.',
                    'history' => 'The Spitfire Mk.I carried RAF Fighter Command through the Battle of Britain in 1940.',
                    'specs' => ['Scale' => '1/48', 'Skill Level' => '2/5', 'Type' => 'Aircraft']],
                'link' => ['xf-82-flat-ocean-gray-2'],
                'paints' => [
                    ['name' => 'Tamiya XF-81 Flat Dark Green 2', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 60, 'specs' => $acrylic('Flat')],
                    ['name' => 'Tamiya XF-83 Flat Medium Sea Gray 2', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 60, 'specs' => $acrylic('Flat')],
                    ['name' => 'Tamiya XF-1 Flat Black', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 100, 'specs' => $acrylic('Flat')],
                ],
                'accessories' => [
                    ['name' => 'Eduard 1/48 Spitfire Mk.I Photo-Etch Detail Set', 'brand' => 'Eduard', 'price' => 245000, 'stock' => 15,
                        'description' => 'Pre-painted etched brass for the cockpit, seatbelts and instrument panel.'],
                    ['name' => 'Eduard 1/48 Spitfire Mk.I Canopy Masks', 'brand' => 'Eduard', 'price' => 98000, 'stock' => 25,
                        'description' => 'Precut Kabuki masks for the canopy and wheel hubs.'],
                ],
            ],
            [
                'kit' => ['name' => 'Tamiya 1/48 Mitsubishi A6M5 Zero Fighter (Zeke)', 'brand' => 'TAMIYA', 'price' => 485000, 'stock' => 25,
                    'description' => 'The late-war Zero, with finely moulded panel lines and a detailed cockpit.',
                    'history' => 'The A6M5 introduced thicker wing skin and improved firepower for the Imperial Japanese Navy.',
                    'specs' => ['Scale' => '1/48', 'Skill Level' => '3/5', 'Type' => 'Aircraft']],
                'link' => [],
                'paints' => [
                    ['name' => 'Tamiya XF-76 Gray Green (IJN)', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 50, 'specs' => $acrylic('Flat')],
                    ['name' => 'Tamiya XF-56 Metallic Gray', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 50, 'specs' => $acrylic('Metallic')],
                    ['name' => 'Tamiya X-7 Red', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 80, 'specs' => $acrylic('Gloss')],
                ],
                'accessories' => [
                    ['name' => 'Eduard 1/48 A6M5 Zero Photo-Etch Detail Set', 'brand' => 'Eduard', 'price' => 265000, 'stock' => 12],
                    ['name' => 'Quinta Studio 1/48 A6M5 Zero 3D Cockpit Set', 'brand' => 'Quinta Studio', 'price' => 385000, 'stock' => 8,
                        'description' => '3D-printed cockpit decals that replace the kit instrument panel and sidewalls.'],
                ],
            ],
            [
                'kit' => ['name' => 'Tamiya 1/35 German Tiger I Early Production', 'brand' => 'TAMIYA', 'price' => 640000, 'stock' => 20,
                    'description' => 'The classic Tiger I with a motorised option, full running gear and a crew figure.',
                    'history' => 'First deployed in 1942, the Tiger I became the feared heavy tank of the Eastern Front.',
                    'specs' => ['Scale' => '1/35', 'Skill Level' => '3/5', 'Type' => 'Armour']],
                'link' => [],
                'paints' => [
                    ['name' => 'Tamiya XF-60 Dark Yellow', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 60, 'specs' => $acrylic('Flat')],
                    ['name' => 'Tamiya XF-61 Dark Green', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 60, 'specs' => $acrylic('Flat')],
                    ['name' => 'Tamiya XF-64 Red Brown', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 60, 'specs' => $acrylic('Flat')],
                ],
                'accessories' => [
                    ['name' => 'Eduard 1/35 Tiger I Photo-Etch Detail Set', 'brand' => 'Eduard', 'price' => 310000, 'stock' => 10],
                    ['name' => 'RB Model 1/35 88 mm KwK 36 Turned Barrel', 'brand' => 'RB Model', 'price' => 275000, 'stock' => 10,
                        'description' => 'Machined aluminium barrel with a hollow muzzle brake.'],
                ],
            ],
            [
                'kit' => ['name' => 'Tamiya 1/24 Toyota GR Supra', 'brand' => 'TAMIYA', 'price' => 760000, 'stock' => 18,
                    'description' => 'Sports-car kit with a detailed engine bay, interior and clear body parts.',
                    'history' => 'The fifth-generation Supra returned in 2019 as a collaboration between Toyota and BMW.',
                    'specs' => ['Scale' => '1/24', 'Skill Level' => '3/5', 'Type' => 'Car']],
                'link' => [],
                'paints' => [
                    ['name' => 'Tamiya TS-14 Black (Spray)', 'brand' => 'TAMIYA', 'price' => 78000, 'stock' => 50, 'specs' => ['Volume' => '100 ml', 'Type' => 'Spray lacquer', 'Finish' => 'Gloss']],
                    ['name' => 'Tamiya X-2 White', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 70, 'specs' => $acrylic('Gloss')],
                    ['name' => 'Tamiya X-11 Chrome Silver', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 50, 'specs' => $acrylic('Metallic')],
                ],
                'accessories' => [
                    ['name' => 'Studio27 1/24 Toyota GR Supra Detail-Up Set', 'brand' => 'Studio27', 'price' => 295000, 'stock' => 10,
                        'description' => 'Etched brass and metal parts for the grille, badges and wipers.'],
                    ['name' => 'Tamiya 1/24 Sports Car Window Masking Set', 'brand' => 'TAMIYA', 'price' => 85000, 'stock' => 20],
                ],
            ],
            [
                'kit' => ['name' => 'Bandai HG 1/144 RX-78-2 Gundam (Revive)', 'brand' => 'Bandai', 'price' => 275000, 'stock' => 35, 'featured' => true,
                    'description' => 'Snap-fit, no glue or paint needed. The easiest way into the hobby.',
                    'history' => 'The original Mobile Suit Gundam of 1979, rebuilt with modern articulation.',
                    'specs' => ['Scale' => '1/144', 'Skill Level' => '1/5', 'Type' => 'Gunpla']],
                'link' => [],
                'paints' => [
                    ['name' => 'Tamiya X-3 Royal Blue', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 60, 'specs' => $acrylic('Gloss')],
                    ['name' => 'Tamiya X-8 Lemon Yellow', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 60, 'specs' => $acrylic('Gloss')],
                    ['name' => 'Tamiya X-7 Red', 'brand' => 'TAMIYA', 'price' => 30000, 'stock' => 80, 'specs' => $acrylic('Gloss')],
                ],
                'accessories' => [
                    ['name' => 'Bandai Builders Parts HD MS Hand 01 (1/144)', 'brand' => 'Bandai', 'price' => 95000, 'stock' => 30,
                        'description' => 'Extra posable hands for 1/144 mobile suits.'],
                    ['name' => 'Bandai Gundam Decal Multiuse (1/144 HG)', 'brand' => 'Bandai', 'price' => 70000, 'stock' => 30],
                ],
            ],
        ];
    }
}

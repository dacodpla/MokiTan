<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brand = fake()->randomElement(['Tamiya', 'Bandai', 'Hasegawa', 'Mr. Hobby', 'GSI Creos', 'Trumpeter', 'Academy']);
        $name = $brand.' '.fake()->unique()->bothify('?###-??');

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'brand' => $brand,
            'price' => fake()->numberBetween(150, 900) * 1000,
            'description' => fake()->paragraph(4),
            'mold_history' => fake()->optional()->paragraph(),
            'stock' => fake()->numberBetween(0, 50),
            'specifications' => [
                'scale' => fake()->randomElement(['1/48', '1/72', '1/144', '1/24', '1/32']),
                'pieces' => fake()->numberBetween(50, 400),
                'skill_level' => fake()->numberBetween(1, 5),
            ],
            'is_featured' => false,
            'image_path' => 'img/products/placeholder-'.fake()->numberBetween(1, 6).'.svg',
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}

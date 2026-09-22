<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $categories = [
            ['name' => 'Kits',      'slug' => 'kits',      'sort_order' => 1],
            ['name' => 'Tools',     'slug' => 'tools',     'sort_order' => 2],
            ['name' => 'Materials', 'slug' => 'materials', 'sort_order' => 3],
            ['name' => 'Paints',    'slug' => 'paints',    'sort_order' => 4],
            ['name' => 'Other',     'slug' => 'other',     'sort_order' => 5],
        ];

        foreach ($categories as $data) {
            Category::create($data + ['image_path' => null]);
        }
    }
}

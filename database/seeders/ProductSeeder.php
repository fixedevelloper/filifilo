<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Store;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::pluck('id')->toArray();
        $categories = Category::pluck('id')->toArray();

        for ($i = 0; $i < 30; $i++) {
            Product::create([
                'name'        => ucfirst(fake()->words(2, true)), // ex: "Pizza Royale"
                'price'       => fake()->randomFloat(2, 500, 10000), // ex: 1500.50
                'imageUrl'    => fake()->imageUrl(640, 480, 'food', true), // ex: https://...
                'store_id'    => fake()->randomElement($stores),
                'category_id' => fake()->randomElement($categories),
            ]);
        }
    }
}

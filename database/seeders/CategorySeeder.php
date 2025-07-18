<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['SHOP', 'RESTAURANT'];

        for ($i = 0; $i < 10; $i++) {
            Category::create([
                'name' => ucfirst(fake()->unique()->word()),
                'type' => fake()->randomElement($types),
            ]);
        }
    }
}

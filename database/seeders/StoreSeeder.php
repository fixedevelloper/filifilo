<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Store;
use App\Models\User;
use App\Models\City;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['SHOP', 'RESTAURANT'];

        $vendors = \App\Models\User::pluck('id')->toArray(); // ou filtrer si besoin
        $cities = \App\Models\City::pluck('id')->toArray();

        for ($i = 0; $i < 10; $i++) {
            Store::create([
                'name'        => ucfirst(fake()->company()),
                'imageUrl'        => fake()->imageUrl(1920, 480, 'shop', true),
                'type'        => fake()->randomElement($types),
                'phone'       => fake()->phoneNumber(),
                'address'     => fake()->address(),
                'latitude'    => fake()->latitude(),
                'longitude'   => fake()->longitude(),
                'time_open'   => fake()->time('H:i'), // ex: 08:00
                'time_close'  => fake()->time('H:i'), // ex: 21:30
                'note'        => fake()->numberBetween(0, 5),
                'is_close'    => fake()->boolean(20), // 20% fermé
                'vendor_id'   => fake()->optional()->randomElement($vendors),
                'city_id'     => fake()->optional()->randomElement($cities),
            ]);
        }
    }
}

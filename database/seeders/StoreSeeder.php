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
        $latlongS = [
            [4.0228757, 9.7197621],
            [4.0565417, 9.7227206],
            [4.0084216, 9.7460029],
            [4.035879135131836, 9.704654693603516]
        ];

        for ($i = 0; $i < 10; $i++) {
            $latlon = $latlongS[array_rand($latlongS)]; // <- choix aléatoire ici

            Store::create([
                'name'        => ucfirst(fake()->company()),
                'imageUrl'    => fake()->imageUrl(1920, 480, 'shop', true),
                'type'        => fake()->randomElement($types),
                'phone'       => fake()->phoneNumber(),
                'address'     => fake()->address(),
                'latitude'    => $latlon[0],
                'longitude'   => $latlon[1],
                'time_open'   => fake()->time('H:i'),
                'time_close'  => fake()->time('H:i'),
                'note'        => fake()->numberBetween(0, 5),
                'is_close'    => fake()->boolean(20),
                'vendor_id'   => fake()->optional()->randomElement($vendors),
                'city_id'     => fake()->optional()->randomElement($cities),
            ]);
        }

    }
}

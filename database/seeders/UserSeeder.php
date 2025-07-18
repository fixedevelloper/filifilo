<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['CLIENT', 'VENDOR', 'ADMIN'];
        $userTypes = [1, 2, 3]; // À adapter selon ta logique métier

        // Créer d’abord quelques parrains (ex: 5)
        $parrains = [];

        for ($i = 0; $i < 5; $i++) {
            $user = User::create([
                'first_name' => fake()->firstName(),
                'last_name'  => fake()->lastName(),
                'phone'      => fake()->phoneNumber(),
                'address'    => fake()->address(),
                'parrain_id' => 0,
                'sold'       => fake()->numberBetween(1000, 10000),
                'last_sold'  => fake()->numberBetween(500, 9000),
                'user_type'  => fake()->randomElement($userTypes),
                'photo'      => null,
                'role'       => 'VENDOR',
                'email'      => fake()->unique()->safeEmail(),
                'activate'   => true,
                'email_verified_at' => now(),
                'password'   => Hash::make('password'), // mot de passe par défaut
            ]);

            $parrains[] = $user->id;
        }

        // Créer 15 utilisateurs avec un parrain aléatoire
        for ($i = 0; $i < 15; $i++) {
            User::create([
                'first_name' => fake()->firstName(),
                'last_name'  => fake()->lastName(),
                'phone'      => fake()->phoneNumber(),
                'address'    => fake()->address(),
                'parrain_id' => fake()->randomElement($parrains),
                'sold'       => fake()->numberBetween(500, 8000),
                'last_sold'  => fake()->numberBetween(300, 7000),
                'user_type'  => fake()->randomElement($userTypes),
                'photo'      => null,
                'role'       => fake()->randomElement($roles),
                'email'      => fake()->unique()->safeEmail(),
                'activate'   => fake()->boolean(90), // 90% activés
                'email_verified_at' => now(),
                'password'   => Hash::make('password'),
            ]);
        }
    }
}

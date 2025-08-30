<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Merchant;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // ----- 1 Admin -----
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'phone' => '600000000',
            'user_type' => 'admin',
        ]);

        // ----- 13 Clients -----
        for ($i = 1; $i <= 13; $i++) {
            $user = User::create([
                'name' => "Client $i",
                'email' => "client$i@example.com",
                'password' => Hash::make('password'),
                'phone' => '69000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'user_type' => 'customer',
            ]);

            // Création du profil client
            Customer::create([
                'user_id' => $user->id,
            ]);
        }

        // ----- 5 Livreurs (Drivers) -----
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => "Driver $i",
                'email' => "driver$i@example.com",
                'password' => Hash::make('password'),
                'phone' => '69800000' . $i,
                'user_type' => 'driver',
            ]);

            // Création du profil driver
            Driver::create([
                'user_id' => $user->id,
                'vehicle_id' => null, // Peut être assigné après
                'current_latitude' => null,
                'current_longitude' => null,
            ]);
        }

        // ----- 6 Merchants -----
        for ($i = 1; $i <= 6; $i++) {
            $user = User::create([
                'name' => "Merchant $i",
                'email' => "merchant$i@example.com",
                'password' => Hash::make('password'),
                'phone' => '69900000' . $i,
                'user_type' => 'merchant',
            ]);

            // Création du profil merchant
            Merchant::create([
                'user_id' => $user->id,
            ]);
        }
    }
}

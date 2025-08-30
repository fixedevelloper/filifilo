<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Merchant;
use App\Models\Store;
use App\Models\Product;
use App\Models\Address;
use App\Models\Vehicle;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Créer un pays par défaut
        $country = Country::create([
            'name' => 'Cameroon',
            'code' => 'CM',
            'default_latitude' => 3.848,
            'default_longitude' => 11.502,
        ]);

// Créer une ville par défaut
        $city = City::create([
            'name' => 'Douala',
            'country_id' => $country->id,
            'default_latitude' => 3.848,
            'default_longitude' => 11.502,
        ]);
        $category = Category::create([
            'name' => 'Category 1',
            'description' => 'Default category',
            //'status' => 'active',
        ]);
        // ----------------- 1 Admin -----------------
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'phone' => '600000000',
            'user_type' => 'admin',
        ]);

        // ----------------- 13 Clients -----------------
        for ($i = 1; $i <= 13; $i++) {
            $user = User::create([
                'name' => "Client $i",
                'email' => "client$i@example.com",
                'password' => Hash::make('password'),
                'phone' => '69000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'user_type' => 'customer',
            ]);

            $customer = Customer::create([
                'user_id' => $user->id,
            ]);

            // Ajouter une adresse par défaut
            Address::create([
                'customer_id' => $customer->id,
                'label' => "Adresse principale",
                'address_line' => "Rue principale $i",
                'latitude' => 3.848,
                'longitude' => 11.502,
                'city_id' => $city->id,
                'country_id' => $country->id,
            ]);

            // Ajouter un moyen de paiement fictif
            PaymentMethod::create([
                'customer_id' => $customer->id,
                'type' => 'card',
                'details' => ['card_number' => '4242424242424242','expiry' => '12/25'],
                'is_default' => true,
            ]);
        }

        // ----------------- 5 Drivers -----------------
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => "Driver $i",
                'email' => "driver$i@example.com",
                'password' => Hash::make('password'),
                'phone' => '69800000' . $i,
                'user_type' => 'driver',
            ]);

            $vehicle = Vehicle::create([
                'brand' => 'Toyota',
                'color' => 'White',
                'type' => 'Car',
                'seats' => 4,
                'registration' => 'ABC-000'.$i,
            ]);

            Driver::create([
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id,
                'current_latitude' => 3.848 + $i*0.01,
                'current_longitude' => 11.502 + $i*0.01,
            ]);
        }

        // ----------------- 6 Merchants -----------------
        for ($i = 1; $i <= 6; $i++) {
            $user = User::create([
                'name' => "Merchant $i",
                'email' => "merchant$i@example.com",
                'password' => Hash::make('password'),
                'phone' => '69900000' . $i,
                'user_type' => 'merchant',
            ]);

            $merchant = Merchant::create([
                'user_id' => $user->id,
            ]);

            // Créer une boutique par défaut
            $store = Store::create([
                'merchant_id' => $merchant->id,
                'name' => "Boutique $i",
                'store_type' => 'restaurant', // ou 'restaurant'
                'city_id' => 1,
                'country_id' => 1,
                'latitude' => 3.848 + $i*0.02,
                'longitude' => 11.502 + $i*0.02,
                'status' => 'active',
                'image_url' => null,
            ]);

            // Ajouter 5 produits par boutique
            for ($j = 1; $j <= 5; $j++) {
                Product::create([
                    'store_id' => $store->id,
                    'category_id' => $category->id,
                    'name' => "Produit $j boutique $i",
                    'description' => "Description produit $j boutique $i",
                    'price' => rand(1000, 10000),
                    'stock_quantity' => 50,
                    'reserved_quantity' => 0,
                    'stock_alert_level' => 5,
                    'status' => 'active',
                    'ingredients' => ['ingredient1','ingredient2'],
                    'addons' => ['addon1','addon2'],
                    'is_deliverable' => true,
                    'is_pickup' => true,
                    'image_url' => null,
                ]);
            }
        }
    }
}

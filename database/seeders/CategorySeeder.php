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
        $shop_categories= ['Africain', 'Indian', 'Chinoise', 'Pizza', 'Japonais', 'Mexicain', 'Italien','Déjeuner / Brunch','Pâtisserie / Boulangerie','Boissons'];
        $repas_par_categorie = [
            'Africain' => ['Thieboudienne', 'Mafé', 'Poulet DG'],
            'Indian' => ['Curry', 'Biryani', 'Naan'],
            'Chinoise' => ['Riz cantonais', 'Nouilles sautées', 'Canard laqué'],
            'Pizza' => ['Margherita', 'Pepperoni', 'Quatre Fromages'],
            'Japonais' => ['Sushi', 'Ramen', 'Sashimi'],
            'Mexicain' => ['Tacos', 'Burrito', 'Fajitas'],
            'Italien' => ['Pâtes Carbonara', 'Lasagnes', 'Risotto']
        ];

        for ($i = 0; $i < sizeof($shop_categories); $i++) {
            Category::create([
                'name' => ucfirst($shop_categories[$i]),
                'type' => $types[1],
            ]);
        }
    }
}

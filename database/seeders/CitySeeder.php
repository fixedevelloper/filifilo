<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('countries')->insert([
            [
                'name' => 'Cameroun',
                'latitude' => '7.3697',
                'longitude' => '12.3547',
                'flag' => '🇨🇲', // ou URL d’image si tu préfères
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Congo',
                'latitude' => '-0.2280',
                'longitude' => '15.8277',
                'flag' => '🇨🇬',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'République Démocratique du Congo',
                'latitude' => '-4.0383',
                'longitude' => '21.7587',
                'flag' => '🇨🇩',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        // Cameroun
        $camerounId = DB::table('countries')->where('name', 'Cameroun')->value('id');
        DB::table('cities')->insert([
            ['country_id' => $camerounId, 'name' => 'Yaoundé', 'latitude' => '3.8480', 'longitude' => '11.5021'],
            ['country_id' => $camerounId, 'name' => 'Douala', 'latitude' => '4.0511', 'longitude' => '9.7679'],
            ['country_id' => $camerounId, 'name' => 'Garoua', 'latitude' => '9.3000', 'longitude' => '13.4000'],
            ['country_id' => $camerounId, 'name' => 'Bafoussam', 'latitude' => '5.4769', 'longitude' => '10.4176'],
            ['country_id' => $camerounId, 'name' => 'Maroua', 'latitude' => '10.5956', 'longitude' => '14.3247'],
        ]);

        // Congo (Brazzaville)
        $congoId = DB::table('countries')->where('name', 'Congo')->value('id');
        DB::table('cities')->insert([
            ['country_id' => $congoId, 'name' => 'Brazzaville', 'latitude' => '-4.2634', 'longitude' => '15.2429'],
            ['country_id' => $congoId, 'name' => 'Pointe-Noire', 'latitude' => '-4.7761', 'longitude' => '11.8636'],
            ['country_id' => $congoId, 'name' => 'Dolisie', 'latitude' => '-4.1983', 'longitude' => '12.6666'],
            ['country_id' => $congoId, 'name' => 'Owando', 'latitude' => '-0.4819', 'longitude' => '15.8996'],
            ['country_id' => $congoId, 'name' => 'Ouesso', 'latitude' => '1.6136', 'longitude' => '16.0510'],
        ]);

        // République Démocratique du Congo (RDC)
        $rdcId = DB::table('countries')->where('name', 'République Démocratique du Congo')->value('id');
        DB::table('cities')->insert([
            ['country_id' => $rdcId, 'name' => 'Kinshasa', 'latitude' => '-4.4419', 'longitude' => '15.2663'],
            ['country_id' => $rdcId, 'name' => 'Lubumbashi', 'latitude' => '-11.6870', 'longitude' => '27.5026'],
            ['country_id' => $rdcId, 'name' => 'Goma', 'latitude' => '-1.6788', 'longitude' => '29.2218'],
            ['country_id' => $rdcId, 'name' => 'Kisangani', 'latitude' => '0.5153', 'longitude' => '25.1909'],
            ['country_id' => $rdcId, 'name' => 'Mbuji-Mayi', 'latitude' => '-6.1500', 'longitude' => '23.6000'],
        ]);
    }

}

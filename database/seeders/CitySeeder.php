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
                'code' => '237',
                'default_latitude' => '7.3697',
                'default_longitude' => '12.3547',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Congo',
                'code' => '242',
                'default_latitude' => '-0.2280',
                'default_longitude' => '15.8277',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'République Démocratique du Congo',
                'code' => '243',
                'default_latitude' => '-4.0383',
                'default_longitude' => '21.7587',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        // Cameroun
        $camerounId = DB::table('countries')->where('name', 'Cameroun')->value('id');
        DB::table('cities')->insert([
            ['country_id' => $camerounId, 'name' => 'Yaoundé', 'default_latitude' => '3.8480', 'default_longitude' => '11.5021'],
            ['country_id' => $camerounId, 'name' => 'Douala', 'default_latitude' => '4.0511', 'default_longitude' => '9.7679'],
            ['country_id' => $camerounId, 'name' => 'Garoua', 'default_latitude' => '9.3000', 'default_longitude' => '13.4000'],
            ['country_id' => $camerounId, 'name' => 'Bafoussam', 'default_latitude' => '5.4769', 'default_longitude' => '10.4176'],
            ['country_id' => $camerounId, 'name' => 'Maroua', 'default_latitude' => '10.5956', 'default_longitude' => '14.3247'],
        ]);

        // Congo (Brazzaville)
        $congoId = DB::table('countries')->where('name', 'Congo')->value('id');
        DB::table('cities')->insert([
            ['country_id' => $congoId, 'name' => 'Brazzaville', 'default_latitude' => '-4.2634', 'default_longitude' => '15.2429'],
            ['country_id' => $congoId, 'name' => 'Pointe-Noire', 'default_latitude' => '-4.7761', 'default_longitude' => '11.8636'],
            ['country_id' => $congoId, 'name' => 'Dolisie', 'default_latitude' => '-4.1983', 'default_longitude' => '12.6666'],
            ['country_id' => $congoId, 'name' => 'Owando', 'default_latitude' => '-0.4819', 'default_longitude' => '15.8996'],
            ['country_id' => $congoId, 'name' => 'Ouesso', 'default_latitude' => '1.6136', 'default_longitude' => '16.0510'],
        ]);

        // République Démocratique du Congo (RDC)
        $rdcId = DB::table('countries')->where('name', 'République Démocratique du Congo')->value('id');
        DB::table('cities')->insert([
            ['country_id' => $rdcId, 'name' => 'Kinshasa', 'default_latitude' => '-4.4419', 'default_longitude' => '15.2663'],
            ['country_id' => $rdcId, 'name' => 'Lubumbashi', 'default_latitude' => '-11.6870', 'default_longitude' => '27.5026'],
            ['country_id' => $rdcId, 'name' => 'Goma', 'default_latitude' => '-1.6788', 'default_longitude' => '29.2218'],
            ['country_id' => $rdcId, 'name' => 'Kisangani', 'default_latitude' => '0.5153', 'default_longitude' => '25.1909'],
            ['country_id' => $rdcId, 'name' => 'Mbuji-Mayi', 'default_latitude' => '-6.1500', 'default_longitude' => '23.6000'],
        ]);
    }

}

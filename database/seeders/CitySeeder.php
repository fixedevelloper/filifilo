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
        DB::table('cities')->insert(
            array (
                0 =>
                    array (
                        'name' => 'Abong Mbang',
                        'latitude' => '3.98333000',
                        'longitude' => '13.18333000',
                    ),
                1 =>
                    array (
                        'name' => 'Akom II',
                        'latitude' => '2.78333000',
                        'longitude' => '10.56667000',
                    ),
                2 =>
                    array (
                        'name' => 'Akono',
                        'latitude' => '3.50000000',
                        'longitude' => '11.33333000',
                    ),
                3 =>
                    array (
                        'name' => 'Akonolinga',
                        'latitude' => '3.76667000',
                        'longitude' => '12.25000000',
                    ),
                4 =>
                    array (
                        'name' => 'Ambam',
                        'latitude' => '2.38333000',
                        'longitude' => '11.28333000',
                    ),
                5 =>
                    array (
                        'name' => 'Babanki',
                        'latitude' => '6.11667000',
                        'longitude' => '10.25000000',
                    ),
                6 =>
                    array (
                        'name' => 'Bafang',
                        'latitude' => '5.15705000',
                        'longitude' => '10.17710000',
                    ),
                7 =>
                    array (
                        'name' => 'Bafia',
                        'latitude' => '4.75000000',
                        'longitude' => '11.23333000',
                    ),
                8 =>
                    array (
                        'name' => 'Bafoussam',
                        'latitude' => '5.47775000',
                        'longitude' => '10.41759000',
                    ),
                9 =>
                    array (
                        'name' => 'Bali',
                        'latitude' => '5.88737000',
                        'longitude' => '10.01176000',
                    ),
                10 =>
                    array (
                        'name' => 'Bamenda',
                        'latitude' => '5.95970000',
                        'longitude' => '10.14597000',
                    ),
                11 =>
                    array (
                        'name' => 'Bamendjou',
                        'latitude' => '5.38988000',
                        'longitude' => '10.33014000',
                    ),
                12 =>
                    array (
                        'name' => 'Bamusso',
                        'latitude' => '4.45910000',
                        'longitude' => '8.90270000',
                    ),
                13 =>
                    array (
                        'name' => 'Bana',
                        'latitude' => '5.14655000',
                        'longitude' => '10.27545000',
                    ),
                14 =>
                    array (
                        'name' => 'Bandjoun',
                        'latitude' => '5.37568000',
                        'longitude' => '10.41326000',
                    ),
                15 =>
                    array (
                        'name' => 'Bangangté',
                        'latitude' => '5.14079000',
                        'longitude' => '10.52535000',
                    ),
                16 =>
                    array (
                        'name' => 'Bankim',
                        'latitude' => '6.08303000',
                        'longitude' => '11.49050000',
                    ),
                17 =>
                    array (
                        'name' => 'Bansoa',
                        'latitude' => '5.44836000',
                        'longitude' => '10.31355000',
                    ),
                18 =>
                    array (
                        'name' => 'Banyo',
                        'latitude' => '6.75000000',
                        'longitude' => '11.81667000',
                    ),
                19 =>
                    array (
                        'name' => 'Batibo',
                        'latitude' => '5.83580000',
                        'longitude' => '9.85530000',
                    ),
                20 =>
                    array (
                        'name' => 'Batouri',
                        'latitude' => '4.43333000',
                        'longitude' => '14.36667000',
                    ),
                21 =>
                    array (
                        'name' => 'Bazou',
                        'latitude' => '5.06001000',
                        'longitude' => '10.46751000',
                    ),
                22 =>
                    array (
                        'name' => 'Bekondo',
                        'latitude' => '4.68190000',
                        'longitude' => '9.32140000',
                    ),
                23 =>
                    array (
                        'name' => 'Bélabo',
                        'latitude' => '4.93333000',
                        'longitude' => '13.30000000',
                    ),
                24 =>
                    array (
                        'name' => 'Bélel',
                        'latitude' => '7.05000000',
                        'longitude' => '14.43333000',
                    ),
                25 =>
                    array (
                        'name' => 'Belo',
                        'latitude' => '6.13333000',
                        'longitude' => '10.25000000',
                    ),
                26 =>
                    array (
                        'name' => 'Bertoua',
                        'latitude' => '4.57728000',
                        'longitude' => '13.68459000',
                    ),
                27 =>
                    array (
                        'name' => 'Bétaré Oya',
                        'latitude' => '5.60000000',
                        'longitude' => '14.08333000',
                    ),
                28 =>
                    array (
                        'name' => 'Bogo',
                        'latitude' => '10.73360000',
                        'longitude' => '14.60928000',
                    ),
                29 =>
                    array (
                        'name' => 'Bonabéri',
                        'latitude' => '4.07142000',
                        'longitude' => '9.68177000',
                    ),
                30 =>
                    array (
                        'name' => 'Boyo',
                        'latitude' => '6.36365000',
                        'longitude' => '10.35540000',
                    ),
                31 =>
                    array (
                        'name' => 'Buea',
                        'latitude' => '4.15342000',
                        'longitude' => '9.24231000',
                    ),
                32 =>
                    array (
                        'name' => 'Diang',
                        'latitude' => '4.25000000',
                        'longitude' => '10.01667000',
                    ),
                33 =>
                    array (
                        'name' => 'Dibombari',
                        'latitude' => '4.17870000',
                        'longitude' => '9.65610000',
                    ),
                34 =>
                    array (
                        'name' => 'Dimako',
                        'latitude' => '4.38333000',
                        'longitude' => '13.56667000',
                    ),
                35 =>
                    array (
                        'name' => 'Dizangué',
                        'latitude' => '3.76667000',
                        'longitude' => '9.98333000',
                    ),
                36 =>
                    array (
                        'name' => 'Djohong',
                        'latitude' => '6.83333000',
                        'longitude' => '14.70000000',
                    ),
                37 =>
                    array (
                        'name' => 'Douala',
                        'latitude' => '4.04827000',
                        'longitude' => '9.70428000',
                    ),
                38 =>
                    array (
                        'name' => 'Doumé',
                        'latitude' => '4.23333000',
                        'longitude' => '13.45000000',
                    ),
                39 =>
                    array (
                        'name' => 'Dschang',
                        'latitude' => '5.44397000',
                        'longitude' => '10.05332000',
                    ),
                40 =>
                    array (
                        'name' => 'Ébolowa',
                        'latitude' => '2.90000000',
                        'longitude' => '11.15000000',
                    ),
                41 =>
                    array (
                        'name' => 'Edéa',
                        'latitude' => '3.80000000',
                        'longitude' => '10.13333000',
                    ),
                42 =>
                    array (
                        'name' => 'Eséka',
                        'latitude' => '3.65000000',
                        'longitude' => '10.76667000',
                    ),
                43 =>
                    array (
                        'name' => 'Essé',
                        'latitude' => '4.10000000',
                        'longitude' => '11.90000000',
                    ),
                44 =>
                    array (
                        'name' => 'Évodoula',
                        'latitude' => '4.08333000',
                        'longitude' => '11.20000000',
                    ),
                45 =>
                    array (
                        'name' => 'Fako Division',
                        'latitude' => '4.16667000',
                        'longitude' => '9.16667000',
                    ),
                46 =>
                    array (
                        'name' => 'Faro Department',
                        'latitude' => '8.25014000',
                        'longitude' => '12.87829000',
                    ),
                47 =>
                    array (
                        'name' => 'Fontem',
                        'latitude' => '5.46850000',
                        'longitude' => '9.88180000',
                    ),
                48 =>
                    array (
                        'name' => 'Foumban',
                        'latitude' => '5.72662000',
                        'longitude' => '10.89865000',
                    ),
                49 =>
                    array (
                        'name' => 'Foumbot',
                        'latitude' => '5.50803000',
                        'longitude' => '10.63250000',
                    ),
                50 =>
                    array (
                        'name' => 'Fundong',
                        'latitude' => '6.25000000',
                        'longitude' => '10.26667000',
                    ),
                51 =>
                    array (
                        'name' => 'Garoua',
                        'latitude' => '9.30143000',
                        'longitude' => '13.39771000',
                    ),
                52 =>
                    array (
                        'name' => 'Garoua Boulaï',
                        'latitude' => '5.88333000',
                        'longitude' => '14.55000000',
                    ),
                53 =>
                    array (
                        'name' => 'Guider',
                        'latitude' => '9.93330000',
                        'longitude' => '13.94671000',
                    ),
                54 =>
                    array (
                        'name' => 'Hauts-Plateaux',
                        'latitude' => '5.29632000',
                        'longitude' => '10.34314000',
                    ),
                55 =>
                    array (
                        'name' => 'Jakiri',
                        'latitude' => '6.10000000',
                        'longitude' => '10.65000000',
                    ),
                56 =>
                    array (
                        'name' => 'Kaélé',
                        'latitude' => '10.10917000',
                        'longitude' => '14.45083000',
                    ),
                57 =>
                    array (
                        'name' => 'Kontcha',
                        'latitude' => '7.96667000',
                        'longitude' => '12.23333000',
                    ),
                58 =>
                    array (
                        'name' => 'Koung-Khi',
                        'latitude' => '5.33848000',
                        'longitude' => '10.47453000',
                    ),
                59 =>
                    array (
                        'name' => 'Kousséri',
                        'latitude' => '12.07689000',
                        'longitude' => '15.03063000',
                    ),
                60 =>
                    array (
                        'name' => 'Koza',
                        'latitude' => '10.86846000',
                        'longitude' => '13.88205000',
                    ),
                61 =>
                    array (
                        'name' => 'Kribi',
                        'latitude' => '2.93725000',
                        'longitude' => '9.90765000',
                    ),
                62 =>
                    array (
                        'name' => 'Kumba',
                        'latitude' => '4.63630000',
                        'longitude' => '9.44690000',
                    ),
                63 =>
                    array (
                        'name' => 'Kumbo',
                        'latitude' => '6.20000000',
                        'longitude' => '10.66667000',
                    ),
                64 =>
                    array (
                        'name' => 'Lagdo',
                        'latitude' => '9.05828000',
                        'longitude' => '13.66605000',
                    ),
                65 =>
                    array (
                        'name' => 'Lebialem',
                        'latitude' => '5.56043000',
                        'longitude' => '9.92316000',
                    ),
                66 =>
                    array (
                        'name' => 'Limbe',
                        'latitude' => '4.02356000',
                        'longitude' => '9.20607000',
                    ),
                67 =>
                    array (
                        'name' => 'Lolodorf',
                        'latitude' => '3.23333000',
                        'longitude' => '10.73333000',
                    ),
                68 =>
                    array (
                        'name' => 'Loum',
                        'latitude' => '4.71820000',
                        'longitude' => '9.73510000',
                    ),
                69 =>
                    array (
                        'name' => 'Makary',
                        'latitude' => '12.57535000',
                        'longitude' => '14.45483000',
                    ),
                70 =>
                    array (
                        'name' => 'Mamfe',
                        'latitude' => '5.75132000',
                        'longitude' => '9.31370000',
                    ),
                71 =>
                    array (
                        'name' => 'Manjo',
                        'latitude' => '4.84280000',
                        'longitude' => '9.82170000',
                    ),
                72 =>
                    array (
                        'name' => 'Maroua',
                        'latitude' => '10.59095000',
                        'longitude' => '14.31593000',
                    ),
                73 =>
                    array (
                        'name' => 'Mayo-Banyo',
                        'latitude' => '6.58138000',
                        'longitude' => '11.73522000',
                    ),
                74 =>
                    array (
                        'name' => 'Mayo-Louti',
                        'latitude' => '9.96577000',
                        'longitude' => '13.72738000',
                    ),
                75 =>
                    array (
                        'name' => 'Mayo-Rey',
                        'latitude' => '8.12630000',
                        'longitude' => '14.61456000',
                    ),
                76 =>
                    array (
                        'name' => 'Mayo-Sava',
                        'latitude' => '11.10682000',
                        'longitude' => '14.20560000',
                    ),
                77 =>
                    array (
                        'name' => 'Mayo-Tsanaga',
                        'latitude' => '10.58221000',
                        'longitude' => '13.79351000',
                    ),
                78 =>
                    array (
                        'name' => 'Mbalmayo',
                        'latitude' => '3.51667000',
                        'longitude' => '11.50000000',
                    ),
                79 =>
                    array (
                        'name' => 'Mbam-Et-Inoubou',
                        'latitude' => '4.73754000',
                        'longitude' => '10.96972000',
                    ),
                80 =>
                    array (
                        'name' => 'Mbandjok',
                        'latitude' => '4.45000000',
                        'longitude' => '11.90000000',
                    ),
                81 =>
                    array (
                        'name' => 'Mbang',
                        'latitude' => '4.58333000',
                        'longitude' => '13.33333000',
                    ),
                82 =>
                    array (
                        'name' => 'Mbanga',
                        'latitude' => '4.50160000',
                        'longitude' => '9.56710000',
                    ),
                83 =>
                    array (
                        'name' => 'Mbankomo',
                        'latitude' => '3.78333000',
                        'longitude' => '11.38333000',
                    ),
                84 =>
                    array (
                        'name' => 'Mbengwi',
                        'latitude' => '6.01667000',
                        'longitude' => '10.00000000',
                    ),
                85 =>
                    array (
                        'name' => 'Mbouda',
                        'latitude' => '5.62611000',
                        'longitude' => '10.25421000',
                    ),
                86 =>
                    array (
                        'name' => 'Mefou-et-Akono',
                        'latitude' => '3.58706000',
                        'longitude' => '11.36089000',
                    ),
                87 =>
                    array (
                        'name' => 'Meïganga',
                        'latitude' => '6.51667000',
                        'longitude' => '14.30000000',
                    ),
                88 =>
                    array (
                        'name' => 'Melong',
                        'latitude' => '5.12181000',
                        'longitude' => '9.96143000',
                    ),
                89 =>
                    array (
                        'name' => 'Mfoundi',
                        'latitude' => '3.86670000',
                        'longitude' => '11.51670000',
                    ),
                90 =>
                    array (
                        'name' => 'Mindif',
                        'latitude' => '10.39757000',
                        'longitude' => '14.43626000',
                    ),
                91 =>
                    array (
                        'name' => 'Minta',
                        'latitude' => '4.58333000',
                        'longitude' => '12.80000000',
                    ),
                92 =>
                    array (
                        'name' => 'Mme-Bafumen',
                        'latitude' => '6.33333000',
                        'longitude' => '10.23333000',
                    ),
                93 =>
                    array (
                        'name' => 'Mokolo',
                        'latitude' => '10.74244000',
                        'longitude' => '13.80227000',
                    ),
                94 =>
                    array (
                        'name' => 'Mora',
                        'latitude' => '11.04611000',
                        'longitude' => '14.14011000',
                    ),
                95 =>
                    array (
                        'name' => 'Mouanko',
                        'latitude' => '3.63972000',
                        'longitude' => '9.77694000',
                    ),
                96 =>
                    array (
                        'name' => 'Mundemba',
                        'latitude' => '4.94790000',
                        'longitude' => '8.87240000',
                    ),
                97 =>
                    array (
                        'name' => 'Mutengene',
                        'latitude' => '4.09130000',
                        'longitude' => '9.31440000',
                    ),
                98 =>
                    array (
                        'name' => 'Muyuka',
                        'latitude' => '4.28980000',
                        'longitude' => '9.41030000',
                    ),
                99 =>
                    array (
                        'name' => 'Mvangué',
                        'latitude' => '2.96667000',
                        'longitude' => '11.51667000',
                    ),
                100 =>
                    array (
                        'name' => 'Mvila',
                        'latitude' => '2.79796000',
                        'longitude' => '11.39434000',
                    ),
                101 =>
                    array (
                        'name' => 'Nanga Eboko',
                        'latitude' => '4.68333000',
                        'longitude' => '12.36667000',
                    ),
                102 =>
                    array (
                        'name' => 'Ndelele',
                        'latitude' => '4.04065000',
                        'longitude' => '14.92501000',
                    ),
                103 =>
                    array (
                        'name' => 'Ndikiniméki',
                        'latitude' => '4.76667000',
                        'longitude' => '10.83333000',
                    ),
                104 =>
                    array (
                        'name' => 'Ndom',
                        'latitude' => '4.49780000',
                        'longitude' => '9.56280000',
                    ),
                105 =>
                    array (
                        'name' => 'Ngambé',
                        'latitude' => '4.23343000',
                        'longitude' => '10.61532000',
                    ),
                106 =>
                    array (
                        'name' => 'Ngaoundéré',
                        'latitude' => '7.32765000',
                        'longitude' => '13.58472000',
                    ),
                107 =>
                    array (
                        'name' => 'Ngomedzap',
                        'latitude' => '3.25000000',
                        'longitude' => '11.20000000',
                    ),
                108 =>
                    array (
                        'name' => 'Ngoro',
                        'latitude' => '4.95000000',
                        'longitude' => '11.38333000',
                    ),
                109 =>
                    array (
                        'name' => 'Ngou',
                        'latitude' => '5.19685000',
                        'longitude' => '10.38595000',
                    ),
                110 =>
                    array (
                        'name' => 'Nguti',
                        'latitude' => '5.32990000',
                        'longitude' => '9.41850000',
                    ),
                111 =>
                    array (
                        'name' => 'Njinikom',
                        'latitude' => '6.23333000',
                        'longitude' => '10.28333000',
                    ),
                112 =>
                    array (
                        'name' => 'Nkongsamba',
                        'latitude' => '4.95470000',
                        'longitude' => '9.94040000',
                    ),
                113 =>
                    array (
                        'name' => 'Nkoteng',
                        'latitude' => '4.51667000',
                        'longitude' => '12.03333000',
                    ),
                114 =>
                    array (
                        'name' => 'Noun',
                        'latitude' => '5.64123000',
                        'longitude' => '10.91840000',
                    ),
                115 =>
                    array (
                        'name' => 'Ntui',
                        'latitude' => '4.45000000',
                        'longitude' => '11.63333000',
                    ),
                116 =>
                    array (
                        'name' => 'Obala',
                        'latitude' => '4.16667000',
                        'longitude' => '11.53333000',
                    ),
                117 =>
                    array (
                        'name' => 'Okoa',
                        'latitude' => '3.98333000',
                        'longitude' => '11.60000000',
                    ),
                118 =>
                    array (
                        'name' => 'Okola',
                        'latitude' => '4.01667000',
                        'longitude' => '11.38333000',
                    ),
                119 =>
                    array (
                        'name' => 'Ombésa',
                        'latitude' => '4.60000000',
                        'longitude' => '11.25000000',
                    ),
                120 =>
                    array (
                        'name' => 'Penja',
                        'latitude' => '4.63911000',
                        'longitude' => '9.67987000',
                    ),
                121 =>
                    array (
                        'name' => 'Pitoa',
                        'latitude' => '9.38390000',
                        'longitude' => '13.50231000',
                    ),
                122 =>
                    array (
                        'name' => 'Poli',
                        'latitude' => '8.47560000',
                        'longitude' => '13.24097000',
                    ),
                123 =>
                    array (
                        'name' => 'Rey Bouba',
                        'latitude' => '8.67240000',
                        'longitude' => '14.17860000',
                    ),
                124 =>
                    array (
                        'name' => 'Saa',
                        'latitude' => '4.36667000',
                        'longitude' => '11.45000000',
                    ),
                125 =>
                    array (
                        'name' => 'Sangmélima',
                        'latitude' => '2.93333000',
                        'longitude' => '11.98333000',
                    ),
                126 =>
                    array (
                        'name' => 'Somié',
                        'latitude' => '6.45843000',
                        'longitude' => '11.43299000',
                    ),
                127 =>
                    array (
                        'name' => 'Tcholliré',
                        'latitude' => '8.40220000',
                        'longitude' => '14.16980000',
                    ),
                128 =>
                    array (
                        'name' => 'Tibati',
                        'latitude' => '6.46504000',
                        'longitude' => '12.62843000',
                    ),
                129 =>
                    array (
                        'name' => 'Tignère',
                        'latitude' => '7.36667000',
                        'longitude' => '12.65000000',
                    ),
                130 =>
                    array (
                        'name' => 'Tiko',
                        'latitude' => '4.07500000',
                        'longitude' => '9.36005000',
                    ),
                131 =>
                    array (
                        'name' => 'Tonga',
                        'latitude' => '4.96667000',
                        'longitude' => '10.70000000',
                    ),
                132 =>
                    array (
                        'name' => 'Vina',
                        'latitude' => '7.16365000',
                        'longitude' => '13.72711000',
                    ),
                133 =>
                    array (
                        'name' => 'Wum',
                        'latitude' => '6.38333000',
                        'longitude' => '10.06667000',
                    ),
                134 =>
                    array (
                        'name' => 'Yabassi',
                        'latitude' => '4.45697000',
                        'longitude' => '9.96822000',
                    ),
                135 =>
                    array (
                        'name' => 'Yagoua',
                        'latitude' => '10.34107000',
                        'longitude' => '15.23288000',
                    ),
                136 =>
                    array (
                        'name' => 'Yaoundé',
                        'latitude' => '3.86667000',
                        'longitude' => '11.51667000',
                    ),
                137 =>
                    array (
                        'name' => 'Yokadouma',
                        'latitude' => '3.51667000',
                        'longitude' => '15.05000000',
                    ),
                138 =>
                    array (
                        'name' => 'Yoko',
                        'latitude' => '5.53333000',
                        'longitude' => '12.31667000',
                    ),
            )

        );
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // database/seeders/CitySeeder.php
    public function run()
    {
        $cities = [
            ['name' => 'Casablanca',  'region' => 'Casablanca-Settat'],
            ['name' => 'Marrakech',   'region' => 'Marrakech-Safi'],
            ['name' => 'Fes',         'region' => 'Fès-Meknès'],
            ['name' => 'Rabat',       'region' => 'Rabat-Salé-Kénitra'],
            ['name' => 'Tangier',     'region' => 'Tanger-Tétouan-Al Hoceïma'],
            ['name' => 'Agadir',      'region' => 'Souss-Massa'],
            ['name' => 'Chefchaouen', 'region' => 'Tanger-Tétouan-Al Hoceïma'],
            ['name' => 'Essaouira',   'region' => 'Marrakech-Safi'],
            ['name' => 'Meknes',      'region' => 'Fès-Meknès'],
            ['name' => 'Ouarzazate',  'region' => 'Drâa-Tafilalet'],
        ];

        foreach ($cities as $city) {
            \App\Models\City::create($city);
        }
    }
}

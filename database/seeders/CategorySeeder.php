<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // database/seeders/CategorySeeder.php
    public function run()
    {
        $categories = ['food', 'beach', 'museum', 'culture', 'show', 'nature'];

        foreach ($categories as $name) {
            \App\Models\Category::create(['name' => $name]);
        }
    }
}

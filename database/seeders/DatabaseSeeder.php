<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        if (! DB::table('menu_items')->exists()) {
            DB::table('menu_items')->insert([
                ['name' => 'Chicken Adobo', 'price' => 180], ['name' => 'Pork Sisig', 'price' => 220],
                ['name' => 'Vegetable Fried Rice', 'price' => 120], ['name' => 'Iced Tea', 'price' => 50],
            ]);
        }
        if (! DB::table('restaurant_tables')->exists()) {
            DB::table('restaurant_tables')->insert([
                ['table_number' => 'T1', 'capacity' => 2], ['table_number' => 'T2', 'capacity' => 4],
                ['table_number' => 'T3', 'capacity' => 6], ['table_number' => 'T4', 'capacity' => 8],
            ]);
        }
    }
}

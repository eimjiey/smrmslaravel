<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class OffenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // These category names match the strings used in your IncidentsSeeder
        $categories = [
            ['name' => 'Minor Offense', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Major Offense', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('offense_categories')->insert($categories);
    }
}
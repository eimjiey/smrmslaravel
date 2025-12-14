<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class OffenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            ['name' => 'Minor Offense', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Major Offense', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('offense_categories')->insert($categories);
    }
}

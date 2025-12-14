<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            \Database\Seeders\ProgramSeeder::class, 
            \Database\Seeders\UserSeeder::class,
            \Database\Seeders\OffenseCategorySeeder::class, 
            \Database\Seeders\OffensesSeeder::class,
            \Database\Seeders\StudentsSeeder::class,
            \Database\Seeders\IncidentsSeeder::class,
        ]);
    }
}

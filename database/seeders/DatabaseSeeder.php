<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\StudentsSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            \Database\Seeders\ProgramSeeder::class, 
            \Database\Seeders\UserSeeder::class,
            
            // --- ORDERED DEPENDENCIES ---
            \Database\Seeders\OffenseCategorySeeder::class, 
            \Database\Seeders\OffensesSeeder::class,        // OffenseCategory must precede Offenses
            \Database\Seeders\StudentsSeeder::class,
            \Database\Seeders\IncidentsSeeder::class,       // Incidents must be last
        ]);
    }
}
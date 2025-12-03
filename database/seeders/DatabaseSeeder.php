<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call UserSeeder
        $this->call([UserSeeder::class]);

        // TODO: Create StudentsSeeder and IncidentsSeeder if needed
        // $this->call(StudentsSeeder::class);
        // $this->call(IncidentsSeeder::class);
        $this->call(StudentsSeeder::class);
        $this->call([IncidentsSeeder::class,]);

    }
}
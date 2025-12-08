<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('programs')->insert([
            // Ensure the first record has ID 1 if possible, or use a specific ID.
            // Laravel's insert() usually auto-increments from 1.
            ['code' => 'BSIT', 'description' => 'Bachelor of Science in Information Technology', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'BSCS', 'description' => 'Bachelor of Science in Computer Science', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'BSIS', 'description' => 'Bachelor of Science in Information Systems', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'BLIS', 'description' => 'Bachelor of Library and Information Science', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'BSDSA', 'description' => 'Bachelor of Science in Data Science and Analytics', 'created_at' => now(), 'updated_at' => now()],
            // ... add the rest of your courses here
        ]);
    }
}
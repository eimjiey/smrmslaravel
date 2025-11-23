<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;   // <--- ADD THIS LINE
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default Admin
        DB::table('users')->insert([
            'name' => 'Misconduct Admin',
            'email' => 'misconductadmin@gmail.com',
            'password' => Hash::make('misconduct@123'), // change to secure password
            'role' => 'admin',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            [
                'name' => 'Mark Justin Balisacan',
                'email' => 'balisacanmarkjustin@gmail.com',
                'password' => Hash::make('eimjiey123'),
                'role' => 'user',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Allysa Reyes',
                'email' => 'reyesallysa@gmail.com',
                'password' => Hash::make('allysa123'),
                'role' => 'user',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

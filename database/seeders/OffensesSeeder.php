<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class OffensesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Get Category IDs (Assuming OffenseCategorySeeder ran first)
        $categoryIds = DB::table('offense_categories')->pluck('id', 'name')->toArray();
        $minorId = $categoryIds['Minor Offense'] ?? 1;
        $majorId = $categoryIds['Major Offense'] ?? 2;

        $offenses = [];

        // Minor Offenses
        $minorNames = [
            'Failure to wear uniform', 'Pornographic materials', 'Littering', 'Loitering',
            'Eating in restricted areas', 'Unauthorized use of school facilities',
            'Lending/borrowing ID', 'Driving violations',
        ];
        foreach ($minorNames as $name) {
            $offenses[] = ['category_id' => $minorId, 'name' => $name, 'created_at' => $now, 'updated_at' => $now];
        }

        // Major Offenses
        $majorNames = [
            'Alcohol/drugs/weapons', 'Smoking', 'Disrespect', 'Vandalism', 'Cheating/forgery',
            'Barricades/obstructions', 'Physical/verbal assault', 'Hazing', 'Harassment/sexual abuse',
            'Unauthorized software/gadgets', 'Unrecognized fraternity/sorority', 'Gambling',
            'Public indecency', 'Offensive/subversive materials', 'Grave threats',
            'Inciting fight/sedition', 'Unauthorized activity', 'Bullying',
        ];
        foreach ($majorNames as $name) {
            $offenses[] = ['category_id' => $majorId, 'name' => $name, 'created_at' => $now, 'updated_at' => $now];
        }

        DB::table('offenses')->insert($offenses);
    }
}
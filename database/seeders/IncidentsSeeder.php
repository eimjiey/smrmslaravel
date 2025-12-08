<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncidentsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Fetch data for lookups
        $students = DB::table('students')->get();
        if ($students->isEmpty()) {
            echo "Warning: No students found in the 'students' table. Skipping IncidentsSeeder.\n";
            return;
        }

        // --- LOOKUPS for Offense IDs ---
        // Assuming your 'offense_categories' table has a 'name' column.
        $categoryIds = DB::table('offense_categories')->pluck('id', 'name')->toArray();
        if (empty($categoryIds)) {
             echo "Error: OffenseCategorySeeder must be run before IncidentsSeeder.\n";
             return;
        }
        
        // Assuming your 'offenses' table has a 'name' column.
        $offenseIds = DB::table('offenses')->pluck('id', 'name')->toArray();
        if (empty($offenseIds)) {
             echo "Error: OffensesSeeder must be run before IncidentsSeeder.\n";
             return;
        }

        // 2. Define data pools for random selection (using names for selection logic)
        $minorOffenses = [
            'Failure to wear uniform', 'Pornographic materials', 'Littering', 'Loitering',
            'Eating in restricted areas', 'Unauthorized use of school facilities',
            'Lending/borrowing ID', 'Driving violations',
        ];

        $majorOffenses = [
            'Alcohol/drugs/weapons', 'Smoking', 'Disrespect', 'Vandalism', 'Cheating/forgery',
            'Barricades/obstructions', 'Physical/verbal assault', 'Hazing', 'Harassment/sexual abuse',
            'Unauthorized software/gadgets', 'Unrecognized fraternity/sorority', 'Gambling',
            'Public indecency', 'Offensive/subversive materials', 'Grave threats',
            'Inciting fight/sedition', 'Unauthorized activity', 'Bullying',
        ];

        $locations = [
            'Room 101', 'Room 203', 'Library', 'Computer Lab', 'Canteen',
            'Hallway - Building A', 'Gymnasium', 'Courtyard',
        ];

        $statuses = ['Pending', 'Investigation', 'Resolved', 'Closed'];
        $months = ['2025-08', '2025-09', '2025-10', '2025-11', '2025-12'];

        // 3. Loop through months to generate incident data
        foreach ($months as $month) {
            $incidentsPerMonth = rand(5, 10);

            for ($i = 0; $i < $incidentsPerMonth; $i++) {
                $student = $students->random();
                $offenseCategoryName = rand(0, 1) === 1 ? 'Minor Offense' : 'Major Offense';
                $specificOffenseName = $offenseCategoryName === 'Minor Offense'
                    ? $minorOffenses[array_rand($minorOffenses)]
                    : $majorOffenses[array_rand($majorOffenses)];
                
                // --- ID ASSIGNMENTS ---
                $categoryId = $categoryIds[$offenseCategoryName] ?? null;
                $specificOffenseId = $offenseIds[$specificOffenseName] ?? null;

                $day = rand(1, 28);
                $date = $month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                $status = $statuses[array_rand($statuses)];

                DB::table('incidents')->insert([
                    'student_id'        => $student->student_number,
                    
                    'date_of_incident'  => $date,
                    'time_of_incident'  => now()->setTime(rand(7, 17), rand(0, 59))->format('H:i:s'),
                    'location'          => $locations[array_rand($locations)],
                    
                    // FIXED: Inserting IDs with correct foreign key names
                    'category_id'       => $categoryId, 
                    'specific_offense_id' => $specificOffenseId, 
                    
                    'description'       => "Student ({$student->student_number}) was reported for: {$specificOffenseName}. Incident occurred at {$locations[array_rand($locations)]}.",
                    'status'            => $status,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }
    }
}
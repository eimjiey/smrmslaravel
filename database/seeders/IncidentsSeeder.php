<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IncidentsSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all students from database
        $students = DB::table('students')->get();

        // Updated Offense lists
        $minorOffenses = [
            'Failure to wear uniform',
            'Pornographic materials',
            'Littering',
            'Loitering',
            'Eating in restricted areas',
            'Unauthorized use of school facilities',
            'Lending/borrowing ID',
            'Driving violations',
        ];

        $majorOffenses = [
            'Alcohol/drugs/weapons',
            'Smoking',
            'Disrespect',
            'Vandalism',
            'Cheating/forgery',
            'Barricades/obstructions',
            'Physical/verbal assault',
            'Hazing',
            'Harassment/sexual abuse',
            'Unauthorized software/gadgets',
            'Unrecognized fraternity/sorority',
            'Gambling',
            'Public indecency',
            'Offensive/subversive materials',
            'Grave threats',
            'Inciting fight/sedition',
            'Unauthorized activity',
            'Bullying',
        ];

        $locations = [
            'Room 101',
            'Room 203',
            'Library',
            'Computer Lab',
            'Canteen',
            'Hallway - Building A',
            'Gymnasium',
            'Courtyard',
        ];

        // Generate 50 random incidents
        for ($i = 0; $i < 50; $i++) {

            $student = $students->random();

            // Randomize offense category
            $isMinor = rand(0, 1) === 1;
            $offenseCategory = $isMinor ? 'Minor Offense' : 'Major Offense';
            $specificOffense = $isMinor
                ? $minorOffenses[array_rand($minorOffenses)]
                : $majorOffenses[array_rand($majorOffenses)];

            DB::table('incidents')->insert([
                'student_id'        => $student->student_number,
                'full_name'         => $student->first_name . ' ' . $student->last_name,
                'program'           => $student->program,
                'year_level'        => $student->year_level,
                'section'           => $student->section,

                'date_of_incident'  => now()->subDays(rand(1, 120))->format('Y-m-d'),
                'time_of_incident'  => now()->setTime(rand(7, 17), rand(0, 59))->format('H:i:s'),
                'location'          => $locations[array_rand($locations)],

                'offense_category'  => $offenseCategory,
                'specific_offense'  => $specificOffense,

                'description'       => "Student was reported for: {$specificOffense}. Incident occurred at the specified location.",
                'status'            => 'Pending',

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }
}

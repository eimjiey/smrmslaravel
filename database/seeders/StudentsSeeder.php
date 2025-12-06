<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentsSeeder extends Seeder
{
    public function run(): void
    {
        // Lists for random data generation
        $courses = ['BSCS', 'BSIS', 'BLIS', 'BSDSA', 'BSIT'];
        $sections = ['1-1', '1-2', '2-1', '2-2', '3-1', '3-2', '4-1', '4-2'];
        $it_sections_advanced = [
            '3-1 WMAD', '3-2 NETSEC',
            '4-1 WMAD', '4-2 NETSEC'
        ]; // Changed 3-1 to WMAD for the IT specializations
        $genders = ['Male', 'Female'];

        // Common names for generating random students (Philippine context)
        $first_names_male = ['Adrian', 'Bryan', 'Charles', 'Daniel', 'Ethan', 'Francis', 'Gabriel', 'Harold', 'Ivan', 'James', 'Kevin', 'Lance', 'Mark', 'Nathan', 'Oscar', 'Paul', 'Quinn', 'Rafael', 'Samuel', 'Troy', 'Uriel', 'Victor', 'William', 'Xavier', 'Yael', 'Zachary'];
        $first_names_female = ['Andrea', 'Bianca', 'Chloe', 'Dianne', 'Ella', 'Faye', 'Grace', 'Hannah', 'Iris', 'Jillian', 'Kate', 'Lorraine', 'Mae', 'Nicole', 'Olive', 'Patricia', 'Queenie', 'Rose', 'Sofia', 'Theresa', 'Ursula', 'Venus', 'Wendy', 'Xyla', 'Yana', 'Zara'];
        $last_names = ['Agbayani', 'Cruz', 'Santos', 'Reyes', 'Garcia', 'Dela Cruz', 'Lopez', 'Gomez', 'Lim', 'Tan', 'Aquino', 'Bautista', 'Santiago', 'Ramos', 'Castro', 'Perez', 'Mendoza', 'Rivera', 'Gonzales', 'Torres', 'Fabian', 'Manalo', 'Zamora', 'Ocampo', 'Villar'];

        // Initial student list
        $students = [
            ['23-063-TS', 'Aquino', 'Christian John', 'aquinochristianjohn84@gmail.com'],
            ['23-0641', 'Asprec', 'Ardelle James', 'ajasprec02@gmail.com'],
            ['22-2626', 'Balisacan', 'Mark Justin', 'balisacanmarkjustin@gmail.com'],
            ['23-0617', 'Baliton', 'Ivan Ruel', 'ivanruelbaliton@gmail.com'],
            ['23-0664', 'Bayucan', 'Anthony Jade', 'jade.0326mar@gmail.com'],
            ['23-0669', 'Benitez', 'Rhea', 'rheabenitez001@gmail.com'],
            ['23-0662', 'Binuya', 'John Lloyd', 'johnyoyd@gmail.com'],
            ['23-0660', 'Blanza', 'Deann Samuel', 'blanzadeannsamuel@gmail.com'],
            ['23-3155-TS', 'Cabucana', 'Renz Reiber', 'renzcabucana07@gmail.com'],
            ['23-0631', 'Candalla', 'Dianne', 'candalladianne@gmail.com'],
            ['23-0655', 'Carabbacan', 'Nicole Faye', 'nicolecarabbacan12@gmail.com'],
            ['23-0671', 'Carino', 'Aliah Marie', 'carinoaliah59@gmail.com'],
            ['23-0645', 'Chavez', 'Donnajane', 'donnachavezjane@gmail.com'],
            ['23-0680', 'Dagdag', 'Czarina', 'dagdagczarina11@gmail.com'],
            ['23-0634', 'Dalangan', 'Harold Tom', 'hdalangan@gmail.com'],
            ['23-0690', 'De Belen', 'Eduard Joseph', 'eduardjoseph.debelen1@gmail.com'],
            ['23-0666', 'Del Rosario', 'Angelo', 'gelo.del13@gmail.com'],
            ['23-0651', 'Dela Cruz', 'Lester', 'delacruzlester1821@gmail.com'],
            ['23-1977', 'Dela Cruz', 'Nicol Jane', 'janenicoldelacruz@gmail.com'],
            ['23-0649', 'Dulatre', 'Mark Daniel', 'dulatredaniel1@gmail.com'],
            ['23-0700', 'Duptitas', 'John Mitchel', 'jmdupitas5@gmail.com'],
            ['23-0629', 'Escolano', 'Jasper', 'jescolano73@gmail.com'],
            ['23-0640', 'Farillon', 'Jhon', 'jhonfarillon@gmail.com'],
            ['23-0678', 'Ferrer', 'Jilmar Caesar', 'jilmarferrer29@gmail.com'],
            ['23-0684', 'Frias', 'Christopher Lance', 'christopherlancefrias@gmail.com'],
            ['23-0615', 'Haduca', 'Jhimson Anthon', 'jhimsonhaduca@gmail.com'],
            ['23-0646', 'Iddurut', 'Justin', 'iddurutjustin23@gmail.com'],
            ['23-0674', 'Idmilaro', 'Liyanne Acosta', 'idmilaoliyanne@gmail.com'],
            ['23-0619', 'Ignacio', 'Dave Raphael', 'daveeeignacio@gmail.com'],
            ['23-0613', 'Ignacio', 'Mitz', 'razzlerivel96@gmail.com'],
            ['23-0624', 'Ingiaen', 'Kathryn Jasmine', 'ingiaenkj@gmail.com'],
            ['23-0638', 'Lagmay', 'Jerome Lee', 'lagmayjerome1@gmail.com'],
            ['23-0648', 'Lazaro', 'Lance Philip', 'lancephilip.lazaro29@gmail.com'],
            ['23-0676', 'Legaspi', 'John Wayne', 'legaspijohnwayne12@gmail.com'],
            ['24-3025', 'Madayag', 'Arman Dave', 'armandave246@gmail.com'],
            ['23-0623', 'Madayag', 'Johnlloyd', 'madayagjohnlloyd25@gmail.com'],
            ['23-0625', 'Mangadap', 'Paul', 'pauljacobmangadap@gmail.com'],
            ['23-0661', 'Manzano', 'Catherine Joy', 'manzanoc878@gmail.com'],
            ['22-0661', 'Maranan', 'Ceejay Austin', 'marananceejayaustin@gmail.com'],
            ['23-0686', 'Matias', 'Ezekiel', 'ezekielmatias09@gmail.com'],
            ['23-0668', 'Miguel', 'J-A', 'mja0935@gmail.com'],
            ['23-0644', 'Padua', 'Princess Zaira', 'zaipadua11@gmail.com'],
            ['20-1055', 'Pascua', 'Gio Levinson', 'giolovinson@gmail.com'],
            ['23-0621', 'Plaza', 'Ezekiel', 'titusplaza1202@gmail.com'],
            ['23-0639', 'Quilang', 'John Kenedy', 'kenedymanaligodbaquiranquilang@gmail.com'],
            ['23-3181-TS', 'Rabago', 'Daniel', 'danielrabago59@gmail.com'],
            ['23-0673', 'Rama', 'Mark Vincent', 'ramamarkvincent2@gmail.com'],
            ['22-0612', 'Reyes', 'Allysa', 'allysareyes@gmail.com'],
            ['23-0687', 'Valdez', 'Janelle', 'janellequinez@gmail.com'],
            ['23-0632', 'Valdez', 'Karl Liam', 'karlliam126@gmail.com'],
            ['23-1980', 'Valdez', 'Mark', 'markvaldez933@gmail.com'],
            ['23-0622', 'Valdez', 'Shaine Paolo', 'paolovaldez69@gmail.com'],
            ['23-0652', 'Villanueva', 'Mhyiel Kyle', 'kyle0568123@gmail.com'],
            ['23-0654', 'Villarta', 'Jamby', 'villartajam02@gmail.com'],
            ['23-3185-TS', 'Vinarao', 'Joaquin', 'vinaraojoaquin@gmail.com'],
            ['24-3051-TS', 'Vinarao', 'Laurina Colleen', 'laurinacolleenvinarao@gmail.com'],
            ['24-3017-TS', 'Zamora', 'Runielle Raven', 'runielle04@gmail.com'],
        ];

        // Helper function to generate a random 4-digit student number suffix
        $generateStudentNumberSuffix = function () {
            return str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        };

        // Generate 50 additional random students
        for ($i = 0; $i < 50; $i++) {
            $gender = $genders[array_rand($genders)];
            $first_names = ($gender === 'Male') ? $first_names_male : $first_names_female;

            $lastName = $last_names[array_rand($last_names)];
            $firstName = $first_names[array_rand($first_names)];

            // Generate a simulated student number
            $yearPrefix = '24-' . $generateStudentNumberSuffix();
            $studentNumber = $yearPrefix;

            // Generate email
            $emailBase = strtolower(str_replace(' ', '', $firstName) . str_replace(' ', '', $lastName));
            $email = $emailBase . mt_rand(1, 100) . '@gmail.com';

            // Randomly select course
            $program = $courses[array_rand($courses)];

            // Randomly select year level (1st, 2nd, 3rd, 4th Year)
            $yearLevelIndex = array_rand([1, 2, 3, 4]);
            $yearLevel = ($yearLevelIndex + 1) . 'st Year';
            if ($yearLevelIndex === 1) $yearLevel = '2nd Year';
            if ($yearLevelIndex === 2) $yearLevel = '3rd Year';
            if ($yearLevelIndex === 3) $yearLevel = '4th Year';

            // Determine section based on program and year level
            $section = $sections[array_rand($sections)];
            if ($program === 'BSIT' && in_array($yearLevel, ['3rd Year', '4th Year'])) {
                // For 3rd/4th year BSIT, use specialized sections
                $section = $it_sections_advanced[array_rand($it_sections_advanced)];
            } else {
                // For other years/programs, determine section based on year_level
                $yearPart = $yearLevelIndex + 1;
                $section = $yearPart . '-' . (array_rand([1, 2]) + 1);
            }


            $students[] = [
                $studentNumber,
                $lastName,
                $firstName,
                $email,
                $gender,
                $program,
                $yearLevel,
                $section,
            ];
        }

        // Insert all students into the database
        foreach ($students as $s) {
            // Check if the array structure has been updated for random students
            if (count($s) === 4) {
                // Original students structure
                list($student_number, $last_name, $first_name, $email) = $s;
                $gender = 'Male'; // Default original gender
                $program = 'BSIT'; // Default original program
                $year_level = '3rd Year'; // Default original year
                $section = '3-1 WMAD'; // Default original section
            } else {
                // Randomly generated students structure
                list($student_number, $last_name, $first_name, $email, $gender, $program, $year_level, $section) = $s;
            }

            DB::table('students')->insert([
                'student_number'    => $student_number,
                'last_name'         => $last_name,
                'first_name'        => $first_name,
                'middle_name'       => null,
                'gender'            => $gender,
                'date_of_birth'     => '2003-01-01', // Static DOB for seeding
                'program'           => $program,
                'year_level'        => $year_level,
                'section'           => $section,
                'contact_number'    => '09' . mt_rand(100000000, 999999999), // Random contact number
                'email'             => strtolower($email),
                'address'           => 'Isabela, Philippines',
                'guardian_name'     => 'Parent/Guardian',
                'guardian_contact'  => '09' . mt_rand(100000000, 999999999), // Random guardian contact
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }
}
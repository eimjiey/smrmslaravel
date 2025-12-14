<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentsSeeder extends Seeder
{
    public function run(): void
    {
        $programs = DB::table('programs')->pluck('id', 'code')->toArray();
        $defaultProgramId = $programs['BSIT'] ?? 1;

        $courses = ['BSCS', 'BSIS', 'BLIS', 'BSDSA', 'BSIT'];
        $genders = ['Male', 'Female'];
        $first_names_male = ['Adrian','Bryan','Charles','Daniel','Ethan','Francis','Gabriel','Harold','Ivan','James','Kevin','Lance','Mark','Nathan','Oscar','Paul','Quinn','Rafael','Samuel','Troy','Uriel','Victor','William','Xavier','Yael','Zachary'];
        $first_names_female = ['Andrea','Bianca','Chloe','Dianne','Ella','Faye','Grace','Hannah','Iris','Jillian','Kate','Lorraine','Mae','Nicole','Olive','Patricia','Queenie','Rose','Sofia','Theresa','Ursula','Venus','Wendy','Xyla','Yana','Zara'];
        $last_names = ['Agbayani','Cruz','Santos','Reyes','Garcia','Dela Cruz','Lopez','Gomez','Lim','Tan','Aquino','Bautista','Santiago','Ramos','Castro','Perez','Mendoza','Rivera','Gonzales','Torres','Fabian','Manalo','Zamora','Ocampo','Villar'];

        $students = [
            ['23-063-TS','Aquino','Christian John','aquinochristianjohn84@gmail.com','Male','3rd Year','3-1 WMAD','BSIT'],
            ['23-0641','Asprec','Ardelle James','ajasprec02@gmail.com','Male','3rd Year','3-1 WMAD','BSIT'],
            ['22-2626','Balisacan','Mark Justin','balisacanmarkjustin@gmail.com','Male','4th Year','4-1 WMAD','BSIT'],
            ['23-0617','Baliton','Ivan Ruel','ivanruelbaliton@gmail.com','Male','3rd Year','3-2 NETSEC','BSIT'],
            ['23-0664','Bayucan','Anthony Jade','jade.0326mar@gmail.com','Male','3rd Year','3-2 NETSEC','BSIT'],
        ];

        $usedStudentNumbers = array_column($students, 0);

        $generateStudentNumberSuffix = function () {
            return str_pad(mt_rand(1,9999),4,'0',STR_PAD_LEFT);
        };

        for ($i=0;$i<50;$i++) {
            $gender = $genders[array_rand($genders)];
            $first_names = ($gender==='Male')?$first_names_male:$first_names_female;
            $lastName = $last_names[array_rand($last_names)];
            $firstName = $first_names[array_rand($first_names)];
            $programCode = $courses[array_rand($courses)];

            do {
                $yearPrefix = '24-'.$generateStudentNumberSuffix();
                $studentNumber = $yearPrefix;
            } while(in_array($studentNumber,$usedStudentNumbers));

            $usedStudentNumbers[] = $studentNumber;
            $emailBase = strtolower(str_replace(' ','',$firstName).str_replace(' ','',$lastName));
            $email = $emailBase.mt_rand(1,1000).$i.'@gmail.com';
            $yearLevelNames = ['1st Year','2nd Year','3rd Year','4th Year'];
            $yearLevelIndex = array_rand($yearLevelNames);
            $yearLevel = $yearLevelNames[$yearLevelIndex];
            $section = ($yearLevelIndex+1).'-'.(array_rand([1,2])+1);

            $students[] = [$studentNumber,$lastName,$firstName,$email,$gender,$yearLevel,$section,$programCode];
        }

        $insertData = [];
        foreach($students as $s) {
            list($student_number,$last_name,$first_name,$email,$gender,$year_level,$section,$programCode) = $s;
            $program_id = $programs[$programCode] ?? $defaultProgramId;
            $insertData[] = [
                'student_number'=>$student_number,
                'last_name'=>$last_name,
                'first_name'=>$first_name,
                'middle_name'=>null,
                'gender'=>$gender,
                'date_of_birth'=>'2003-01-01',
                'program_id'=>$program_id,
                'year_level'=>$year_level,
                'section'=>$section,
                'contact_number'=>'09'.mt_rand(100000000,999999999),
                'email'=>strtolower($email),
                'address'=>'Isabela, Philippines',
                'guardian_name'=>'Parent/Guardian',
                'guardian_contact'=>'09'.mt_rand(100000000,999999999),
                'created_at'=>now(),
                'updated_at'=>now(),
            ];
        }

        DB::table('students')->insert($insertData);
    }
}

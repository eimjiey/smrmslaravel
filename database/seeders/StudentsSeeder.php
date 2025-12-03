<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentsSeeder extends Seeder
{
    public function run(): void
    {
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

        foreach ($students as $s) {
            DB::table('students')->insert([
                'student_number'  => $s[0],
                'last_name'       => $s[1],
                'first_name'      => $s[2],
                'middle_name'     => null,
                'gender'          => 'Male', // default (change if needed)
                'date_of_birth'   => '2003-01-01',
                'program'         => 'BSIT',
                'year_level'      => '3rd Year',
                'section'         => '3-1 WMAD',
                'contact_number'  => '09123456789',
                'email'           => strtolower($s[3]),
                'address'         => 'Isabela, Philippines',
                'guardian_name'   => 'Parent/Guardian',
                'guardian_contact'=> '09112223344',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}

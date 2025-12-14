<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id('student_id');
            $table->string('student_number', 10)->unique(); 

            $table->string('last_name', 100);
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->date('date_of_birth');
            $table->text('address');
            $table->string('guardian_name', 150);
            $table->string('guardian_contact', 11);

            $table->foreignId('program_id')
                ->constrained('programs')
                ->onDelete('restrict');

            $table->enum('year_level', ['1st Year', '2nd Year', '3rd Year', '4th Year']); 
            $table->string('section', 50)->nullable();

            $table->string('contact_number', 11)->unique(); 
            $table->string('email', 150)->unique(); 

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

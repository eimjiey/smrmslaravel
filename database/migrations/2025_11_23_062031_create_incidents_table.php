<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            
            $table->string('student_id')->unique();
            $table->string('full_name');
            $table->string('program')->nullable();
            $table->string('year_level');
            $table->string('section')->nullable();
            
            $table->date('date_of_incident');
            $table->time('time_of_incident');
            $table->string('location');
            
            $table->string('offense_category'); 
            $table->string('specific_offense'); 
            
            $table->text('description');
            
            $table->string('status')->default('Pending'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
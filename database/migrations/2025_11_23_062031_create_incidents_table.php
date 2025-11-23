<?php
// database/migrations/YYYY_MM_DD_create_incidents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            
            // Student Information
            $table->string('student_id')->unique();
            $table->string('full_name');
            $table->string('program')->nullable();
            $table->string('year_level');
            $table->string('section')->nullable();
            
            // Incident Details
            $table->date('date_of_incident');
            $table->time('time_of_incident');
            $table->string('location');
            
            // Offense Type (The two-tier structure)
            $table->string('offense_category'); // e.g., 'Minor Offense', 'Major Offense'
            $table->string('specific_offense'); // e.g., 'Failure to wear uniform', 'Cheating/forgery'
            
            // Description
            $table->text('description');
            
            // System Tracking
            $table->string('status')->default('Pending'); // For tracking report status
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
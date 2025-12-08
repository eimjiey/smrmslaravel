<?php
// ..._create_discipline_history_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_history', function (Blueprint $table) {
            $table->id();
            
            // --- FIX: Explicitly reference the custom primary key 'student_id' ---
            $table->unsignedBigInteger('student_id'); // Must match the type of the PK
            $table->foreign('student_id')
                  ->references('student_id') // <--- Reference the actual PK name
                  ->on('students')
                  ->onDelete('cascade');
            // ------------------------------------------------------------------
            
            // Incident ID reference is likely fine, but we'll use the long form to be safe:
            $table->unsignedBigInteger('incident_id');
            $table->foreign('incident_id')
                  ->references('id') // Incidents uses the default 'id' PK
                  ->on('incidents')
                  ->onDelete('cascade');
                  
            $table->string('action_taken', 255);
            $table->date('date_executed');
            $table->timestamps();
        });
    }
    // ... down method ...
};
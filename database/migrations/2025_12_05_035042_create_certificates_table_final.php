<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_name');
            $table->string('student_id', 50)->index();
            $table->string('program_grade')->nullable();
            $table->string('offense_type');
            $table->date('date_of_incident');
            $table->text('disciplinary_action');
            $table->string('status', 20);
            $table->string('certificate_number')->unique();
            $table->date('issued_date');
            $table->string('school_name')->nullable();
            $table->string('school_location')->nullable();
            $table->string('official_name')->nullable();
            $table->string('official_position')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};

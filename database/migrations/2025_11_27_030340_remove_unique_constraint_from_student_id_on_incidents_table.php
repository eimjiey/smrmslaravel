<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations (Removes the unique constraint).
     */
    public function up(): void
    {
        // CORRECT: Removes the constraint, allowing duplicates.
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropUnique(['student_id']);
        });
    }

    public function down(): void
    {
        // CAUSE OF ERROR: Tries to add constraint back, but it might already exist.
        Schema::table('incidents', function (Blueprint $table) {
            $table->unique('student_id');
        });
    }
};
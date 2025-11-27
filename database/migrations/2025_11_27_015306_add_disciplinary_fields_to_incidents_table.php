<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            // New column to store the system's calculated recommendation
            $table->string('recommendation')->nullable()->after('specific_offense');
            
            // New column to store the final action taken by the administrator
            // This is crucial for future re-training/optimization.
            $table->string('action_taken')->nullable()->after('recommendation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('action_taken');
            $table->dropColumn('recommendation');
        });
    }
};
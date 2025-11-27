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
            // Adds the filer_id column as a string, placed after the 'id' column.
            $table->string('filer_id')->nullable()->after('id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            // 1. Drop the foreign key constraint first
            $table->dropForeign(['filer_id']); 
            
            // 2. Then, drop the column
            $table->dropColumn('filer_id');
        });
    }
};
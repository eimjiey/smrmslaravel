<?php
// database/migrations/OLDER_TIMESTAMP_create_offenses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offenses', function (Blueprint $table) {
            $table->id(); // Default UNSIGNED BIGINT PRIMARY KEY
            $table->string('name', 150)->unique();
            $table->text('penalty')->nullable();
            
            // Link to the Category table
            $table->foreignId('category_id')
                  ->constrained('offense_categories')
                  ->onDelete('cascade');
                  
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offenses');
    }
};
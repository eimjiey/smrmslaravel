<?php

// database/migrations/OLDER_TIMESTAMP_create_offense_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offense_categories', function (Blueprint $table) {
            $table->id(); // Default UNSIGNED BIGINT PRIMARY KEY
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offense_categories');
    }
};

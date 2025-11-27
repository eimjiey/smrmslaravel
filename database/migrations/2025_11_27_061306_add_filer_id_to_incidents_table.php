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
        $table->string('filer_id')->nullable()->after('id'); 
        // Note: Use string if your user_abc123 IDs are strings, 
        // otherwise use unsignedBigInteger if they are standard numeric user IDs.
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            //
        });
    }
};

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
        Schema::create('logins_histories', function (Blueprint $table) {
            $table->id();
            // Foreign key constrained to the 'users' table
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // For tracking device, IP, and the result of the login attempt
            $table->string('email_attempted')->index();
            $table->string('device')->nullable();
            $table->enum('status', ['success', 'failure'])->index();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logins_histories');
    }
};
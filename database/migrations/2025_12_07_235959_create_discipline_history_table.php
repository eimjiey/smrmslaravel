<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('students')
                  ->onDelete('cascade');
            $table->unsignedBigInteger('incident_id');
            $table->foreign('incident_id')
                ->references('id')
                ->on('incidents')
                ->onDelete('cascade');
            $table->string('action_taken', 255);
            $table->date('date_executed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_history');
    }
};

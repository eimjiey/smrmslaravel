<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('filer_id');
            $table->string('student_id', 10);
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('specific_offense_id');
            $table->date('date_of_incident');
            $table->time('time_of_incident');
            $table->string('location', 255);
            $table->text('description');
            $table->string('status', 50)->default('Pending');
            $table->text('disciplinary_action')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('filer_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('offense_categories');
            $table->foreign('specific_offense_id')->references('id')->on('offenses');
            
            $table->index(['date_of_incident', 'status']);
        });
        
        Schema::create('incident_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->onDelete('cascade');
            $table->string('action_type', 10);
            $table->string('field_changed', 100);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_audit_logs');
        Schema::dropIfExists('incidents');
    }
};
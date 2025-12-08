<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_audit_logs', function (Blueprint $table) {
            $table->id();
            // Link back to the incident that was changed
            $table->foreignId('incident_id')->constrained('incidents')->onDelete('cascade');
            $table->string('action_type', 10); // e.g., 'UPDATE'
            $table->string('field_changed', 100); 
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_audit_logs');
    }
};
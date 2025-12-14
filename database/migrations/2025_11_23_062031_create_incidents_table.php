<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            $table->string('student_id', 10);
            $table->foreign('student_id')
                  ->references('student_number')
                  ->on('students')
                  ->onDelete('restrict');

            $table->foreignId('incharge_user_id')->nullable()->constrained('users');

            $table->foreignId('category_id')->constrained('offense_categories');
            $table->foreignId('specific_offense_id')->constrained('offenses');

            $table->date('date_of_incident');
            $table->time('time_of_incident');
            $table->string('location');
            $table->text('description');

            $table->enum('status', ['Pending', 'Investigation', 'Resolved', 'Closed'])
                  ->default('Pending');

            $table->string('disciplinary_action', 255)->nullable();
            $table->timestamps();
        });

        $triggerSql = "
            CREATE TRIGGER trg_incident_update_audit
            AFTER UPDATE ON incidents
            FOR EACH ROW
            BEGIN
                IF OLD.status <> NEW.status THEN
                    INSERT INTO incident_audit_logs (incident_id, action_type, field_changed, old_value, new_value)
                    VALUES (NEW.id, 'UPDATE', 'status', OLD.status, NEW.status);
                END IF;

                IF OLD.disciplinary_action <> NEW.disciplinary_action THEN
                    INSERT INTO incident_audit_logs (incident_id, action_type, field_changed, old_value, new_value)
                    VALUES (NEW.id, 'UPDATE', 'disciplinary_action', OLD.disciplinary_action, NEW.disciplinary_action);
                END IF;
            END;
        ";

        DB::unprepared($triggerSql);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_incident_update_audit;');
        Schema::dropIfExists('incidents');
    }
};

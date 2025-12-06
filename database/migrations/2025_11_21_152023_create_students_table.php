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
            Schema::create('students', function (Blueprint $table) {
                $table->id('student_id'); // Primary Key
                $table->string('student_number', 10)->unique(); 
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('middle_name', 100)->nullable();
                $table->enum('gender', ['Male', 'Female', 'Other']);
                $table->date('date_of_birth');
                $table->string('program', 150);
                $table->enum('year_level', ['1st Year', '2nd Year', '3rd Year', '4th Year']);
                $table->string('section', 50)->nullable();
                $table->string('contact_number', 11);
                $table->string('email', 150)->unique();
                $table->text('address'); 
                $table->string('guardian_name', 150);
                $table->string('guardian_contact', 11);
                $table->timestamps(); // includes created_at & updated_at
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('students');
        }
    };

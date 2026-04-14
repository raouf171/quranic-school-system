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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seance_id')
                  ->constrained('seances')
                  ->onDelete('cascade');

            $table->foreignId('student_id')
                  ->constrained('students')
                  ->onDelete('cascade');
            
            
                  
            $table->enum('status', [
                'present',
                'absent',
                'late',
                'excused'
            ]);

            $table ->string('evaluation_grade',10)->nullable() ; 
            $table-> unsignedInteger ('points')->default(0) ; 


        $table->unique(['seance_id', 'student_id']);
            $table->index('student_id');
            $table->index('seance_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

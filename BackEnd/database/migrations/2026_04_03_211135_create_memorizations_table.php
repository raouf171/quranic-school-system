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
        Schema::create('memorizations', function (Blueprint $table) {
            $table->id();

           $table->foreignId('seance_id')
                  ->constrained('seances')
                  ->onDelete('cascade');

           $table->foreignId('student_id')
                  ->constrained('students')
                  ->onDelete('cascade');

            $table->foreignId('evaluation_id')
                  ->constrained('evaluations')
                  ->onDelete('cascade');

            
                  $table->unsignedInteger('surah_start') ; 
                  $table->unsignedInteger('verse_start') ; 
                  $table->unsignedInteger('surah_end') ; 
                  $table->unsignedInteger('verse_end') ;

                  $table ->string('evaluation_grade',10)->nullable() ; 
            $table-> unsignedInteger ('points')->default(0) ; 

                  
                  
            $table->index(['student_id', 'seance_id']);
    
 
                  



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memorizations');
    }
};

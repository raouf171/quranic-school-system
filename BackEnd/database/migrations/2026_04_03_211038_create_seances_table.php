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
        Schema::create('seances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('halaqa_id')
                  ->constrained('halaqat')
                  ->onDelete('cascade');

            $table->foreignId('created_by') 
                  ->constrained('teachers')
                  ->onDelete('cascade');

            $table->foreignId('classroom_id')
                  ->nullable()
                  ->constrained('classrooms')
                  ->nullOnDelete();
            
             $table->text('notes')->nullable();

             $table ->date('date') ; 

            

            $table->timestamps();

            $table->unique(['halaqa_id', 'date']); // here i ùake sure the seance is only for one halaqa in only on singe day 
             
            $table->index(['halaqa_id', 'date']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    // Drop the FK constraint FIRST before dropping the table
    Schema::table('seances', function (Blueprint $table) {
        $table->dropForeign(['halaqa_id']);
    });

    Schema::dropIfExists('seances');
}
};

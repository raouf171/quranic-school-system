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
            $table->id();

            $table ->foreignId('parent_id')
                   ->nullOnDelete()
                   ->constrained('parents')  ; 

             $table->foreignId('halaqa_id')
                  ->nullable()
                  ->constrained('halaqat')
                  ->nullOnDelete();

            $table->string('full_name', 100);
            $table->date('birth_date')->nullable();

            $table->enum('social_state', [
                'normal',
                'father_deceased',
                'mother_deceased',
                'divorced_parents'
            ])->default('normal');

            $table->enum('fee_status', [
                'paid',
                'pending',
                'late',
                'exempt'
            ])->default('pending');

               //hnaa i used index to maximize the searching speed , cut we need to list students by halaqa , by fathers...etc
            $table->index('halaqa_id');
            $table->index('parent_id');






            $table->timestamps();
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


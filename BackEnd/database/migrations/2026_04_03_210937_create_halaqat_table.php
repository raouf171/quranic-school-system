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
        Schema::create('halaqat', function (Blueprint $table) {
            $table->id();

            $table ->foreignId('teacher_id')
                    ->unique()
                   ->nullOnDelete()
                   ->constrained('teachers') ;   

            $table ->string ('name' ,100) ; 
            
            $table -> string ('schedule')->nullable() ;
            
            $table ->unsignedInteger ('maxx_students') 
                  ->default(30) ; 

            $table ->boolean('is_active')
                    ->default(true) ; 



            $table->timestamps();



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('halaqat');
    }
};

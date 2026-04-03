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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                    ->unique()
                   ->onDelete('cascade')
                   ->constrained('accounts')  ; 

            $table ->string ('name') ; 
            $table ->date('hiring_date')->nullable() ; 
            $table ->boolean ('is_available')->default(true) ; 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};

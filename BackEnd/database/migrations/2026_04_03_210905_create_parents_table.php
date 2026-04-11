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
        Schema::create('parents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                    ->unique()
                   ->onDelete('cascade')
                   ->constrained('accounts')  ; 
            
             $table ->string ('name') ; 
            
             $table ->string('occupation')->nullable() ; //his job khedemto
             $table ->text ('address') ->nullable() ; 
             
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};

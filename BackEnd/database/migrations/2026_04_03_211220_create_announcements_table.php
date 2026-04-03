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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table -> string('title' , 200);
            $table ->text('content') ; 
 



             $table->foreignId('created_by')
                  ->constrained('admins')
                  ->onDelete('cascade');


                $table->json('target_roles'); // if yhou dont understand this ask me nfahmkom 'i know no one eading the code anyways"
 
            $table->date('expiry_date')->nullable();


                        $table->timestamps();


             $table->index('created_at'); //ordered 'newest , olderst...etc"
        });
    }
   
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};

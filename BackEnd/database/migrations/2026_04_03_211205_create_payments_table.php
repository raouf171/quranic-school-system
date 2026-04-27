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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->onDelete('cascade');

            $table->string('month', 20);

            $table->decimal('amount', 10, 2);

            $table->date('due_date');

            $table->date('paid_date')->nullable();


            $table->enum('status', [
                'paid',
                'pending',
                'late',
                'exempt'
            ])->default('pending');


         $table->unique(['student_id', 'month']);
        $table->index('student_id');

            $table->index('status') ; 




            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
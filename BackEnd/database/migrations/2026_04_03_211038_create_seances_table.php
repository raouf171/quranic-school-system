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

            $table->foreignId('schedule_id')
                  ->nullable()
                  ->constrained('halaqa_schedules')
                  ->nullOnDelete();

            $table->date('occurrence_date');

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->enum('status', ['scheduled', 'held', 'cancelled'])
                  ->default('scheduled');

            $table->text('cancel_reason')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('date_id')
                  ->nullable()
                  ->constrained('dates')
                  ->nullOnDelete();

            $table->timestamps();

            // One materialized row per scheduled occurrence.
            $table->unique(['halaqa_id', 'schedule_id', 'occurrence_date'], 'seance_occurrence_unique');
            $table->index(['halaqa_id', 'occurrence_date']);
            $table->index(['created_by', 'occurrence_date']);
            $table->index(['status', 'occurrence_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seances');
    }
};

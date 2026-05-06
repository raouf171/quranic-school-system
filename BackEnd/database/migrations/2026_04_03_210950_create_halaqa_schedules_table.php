<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('halaqa_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('halaqa_id')
                ->constrained('halaqat')
                ->cascadeOnDelete();

            // 0=Sunday, 1=Monday, ... 6=Saturday
            $table->unsignedTinyInteger('weekday');

            $table->time('start_time');
            $table->time('end_time');

            $table->foreignId('classroom_id')
                ->nullable()
                ->constrained('classrooms')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);

            $table->timestamps();

            $table->index(['halaqa_id', 'weekday']);
            $table->index(['classroom_id', 'weekday']);

            // Avoid exact duplicate slots in the same halaqa.
            $table->unique(['halaqa_id', 'weekday', 'start_time', 'end_time', 'classroom_id'], 'halaqa_schedule_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('halaqa_schedules');
    }
};

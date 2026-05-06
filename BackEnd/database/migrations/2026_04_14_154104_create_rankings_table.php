<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();

            // Ranking = par étudiant par halaqa
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->onDelete('cascade');

            $table->foreignId('halaqa_id')
                  ->constrained('halaqat')
                  ->onDelete('cascade');

            // Score calculé automatiquement par l'Observer
            // présent=+1, absent=-1, late/excused=0
            // + points des évaluations memorization/revision
            $table->integer('score')->default(0);

            // Position dans la halaqa
            // null = pas encore calculé par admin
            $table->unsignedInteger('rank')->nullable();

            // weekly ou monthly
            $table->enum('period_type', ['weekly', 'monthly']);

            // Période couverte par ce ranking
            $table->date('period_start');
            $table->date('period_end');

            // Quand ce calcul a été fait
            $table->timestamp('calculated_at');

            // Index pour requêtes fréquentes
            $table->index(['student_id', 'halaqa_id']);
            $table->index('period_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};
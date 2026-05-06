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
    Schema::table('memorizations', function (Blueprint $table) {
        $table->foreignId('teacher_id')
              ->nullable()
              ->after('student_id')
              ->constrained('teachers')
              ->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('memorizations', function (Blueprint $table) {
        $table->dropForeign(['teacher_id']);
        $table->dropColumn('teacher_id');
    });

    }
};

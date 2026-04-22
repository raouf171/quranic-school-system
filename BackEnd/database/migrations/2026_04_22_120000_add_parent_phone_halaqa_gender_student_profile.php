<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parents', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('name');
        });

        Schema::table('halaqat', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])
                ->default('male')
                ->after('name');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])
                ->default('male')
                ->after('full_name');

            $table->string('photo_path', 500)->nullable()->after('gender');

            $table->enum('relationship_nature', [
                'mother',
                'father',
                'uncle',
                'aunt',
                'grandfather',
                'grandmother',
                'legal_guardian',
                'other',
            ])->default('father')->after('photo_path');

            $table->enum('school_level', [
                'kindergarten',
                'primary',
                'middle_cem',
                'high_school',
                'university',
                'other',
            ])->default('primary')->after('relationship_nature');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'photo_path',
                'relationship_nature',
                'school_level',
            ]);
        });

        Schema::table('halaqat', function (Blueprint $table) {
            $table->dropColumn('gender');
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};

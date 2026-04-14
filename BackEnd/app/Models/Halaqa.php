<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Halaqa extends Model
{
    protected $table = 'halaqat';

    protected $fillable = [
        'teacher_id',
        'name',
        'schedule',
        'max_students',      // ← FIXED: was 'maxx_students'
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_students' => 'integer',   // ← FIXED: was 'max_student'
    ];

    // Relationships

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function students()           // ← FIXED: was 'studensts'
    {
        return $this->hasMany(Student::class, 'halaqa_id');
    }

    public function seances()            // ← FIXED: was 'seacnes'
    {
        return $this->hasMany(Seance::class, 'halaqa_id');
    }

    // Check if Halaqa is full
    public function isFull(): bool
    {
        return $this->students()->count() >= $this->max_students;
    }
}
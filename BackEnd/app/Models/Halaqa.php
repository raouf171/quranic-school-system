<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Halaqa extends Model
{
    protected $table = 'halaqat';

    protected $fillable = [
        'teacher_id',
        'name',
        'gender',
        'schedule',
        'max_students',      // ← FIXED: was 'maxx_students'
        'is_active',
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

    public function nextSeance(): ?Seance
    {
        return $this->seances()
            ->join('dates', 'seances.date_id', '=', 'dates.id')
            ->whereDate('dates.date_value', '>=', today())
            ->orderBy('dates.date_value')
            ->select('seances.*')
            ->with(['dateEntry', 'classroom'])
            ->first();
    }

    public function getNextSeanceSummaryAttribute(): ?array
    {
        $nextSeance = $this->nextSeance();

        if (! $nextSeance) {
            return null;
        }

        return [
            'id' => $nextSeance->id,
            'date' => $nextSeance->dateEntry?->date_value?->format('Y-m-d'),
            'schedule' => $this->schedule,
            'classroom' => $nextSeance->classroom ? [
                'id' => $nextSeance->classroom->id,
                'name' => $nextSeance->classroom->name,
                'building' => $nextSeance->classroom->building,
            ] : null,
        ];
    }

    // Check if Halaqa is full
    public function isFull(): bool
    {
        return $this->students()->count() >= $this->max_students;
    }
}
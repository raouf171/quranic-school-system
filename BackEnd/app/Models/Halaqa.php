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

    public function schedules()
    {
        return $this->hasMany(HalaqaSchedule::class, 'halaqa_id');
    }

    public function nextSeance(): ?Seance
    {
        return $this->seances()
            ->whereDate('occurrence_date', '>=', today())
            ->where('status', '!=', 'cancelled')
            ->orderBy('occurrence_date')
            ->orderBy('start_time')
            ->with(['classroom', 'schedule'])
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
            'date' => optional($nextSeance->occurrence_date)->format('Y-m-d'),
            'schedule' => $nextSeance->schedule ? [
                'id' => $nextSeance->schedule->id,
                'weekday' => $nextSeance->schedule->weekday,
                'start_time' => $nextSeance->schedule->start_time,
                'end_time' => $nextSeance->schedule->end_time,
            ] : null,
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
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendances';
    
    protected $fillable = [
        'seance_id',
        'student_id',
        'status',
        'evaluation_grade',
        'points',
        
    ];

    protected $casts = [
        'status' => 'string',
        'points' => 'integer',
    ];

    // relations 
    public function seance()
    {
        return $this->belongsTo(Seance::class, 'seance_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(Teacher::class, 'recorded_by');
    }
}
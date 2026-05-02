<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    protected $table = 'revisions';
    
    protected $fillable = [
        'seance_id',
        'student_id',
        'evaluation_id',
        'surah_start',
        'verse_start',
        'surah_end',
        'verse_end',
        'evaluation_grade',
        'points'  
    ];

    protected $casts = [
        'surah_start' => 'integer',
        'verse_start' => 'integer',         
        'surah_end' => 'integer',
        'verse_end' => 'integer',
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

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    protected $table = 'seances'; 
    
    protected $fillable = [
        'halaqa_id', 
        'created_by',
        'classroom_id', 
        'date_id',
        'notes'
    ];

    protected $casts = [];

    // Relationship to DateEntry model
    public function dateEntry()  // ← Renamed for clarity
    {
        return $this->belongsTo(DateEntry::class, 'date_id');
    }

    // Accessor to get the actual date
    public function getDateValueAttribute()
    {
        return $this->dateEntry?->date_value;
    }

    // Keep old 'date' relationship for backward compatibility
    public function date()
    {
        return $this->belongsTo(DateEntry::class, 'date_id');
    }

    // Rest of your relations
    public function halaqa()
    {
        return $this->belongsTo(Halaqa::class, 'halaqa_id'); 
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'created_by'); 
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id'); 
    }

    public function memorizations()
    {
        return $this->hasMany(Memorization::class, 'seance_id'); 
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class, 'seance_id'); 
    }

  

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'seance_id');
    }
}
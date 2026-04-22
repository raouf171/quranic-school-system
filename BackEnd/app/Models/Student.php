<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    protected $table = 'students' ; 

    protected $fillable = [
        'parent_id',
        'halaqa_id',
        'full_name',
        'gender',
        'photo_path',
        'relationship_nature',
        'school_level',
        'birth_date',
        'social_state',
        'fee_status',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    protected $appends = [
        'photo_url',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->photo_path);
    }

    //relation
    public function parent()
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }

public function halaqa()
    {
        return $this->belongsTo(Halaqa::class, 'halaqa_id');
    }

public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function memorizations()
    {
        return $this->hasMany(Memorization::class, 'student_id');
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class, 'student_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'student_id');
    }

    //halper functions
    public function isExempt(): bool
    {
        return $this->fee_status === 'exempt';
    }
    
}

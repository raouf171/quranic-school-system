<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $table = 'classrooms';

    protected $fillable = [
        'name', 'building', 'capacity', 'is_available'
    ];

    protected $casts = [ 
        'is_available' => 'boolean',  
        'capacity' => 'integer',  
    ];

    public function seances() {
        return $this->hasMany(Seance::class, 'classroom_id');
    }

    public function halaqaSchedules()
    {
        return $this->hasMany(HalaqaSchedule::class, 'classroom_id');
    }
}
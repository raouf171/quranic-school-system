<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HalaqaSchedule extends Model
{
    protected $table = 'halaqa_schedules';

    protected $fillable = [
        'halaqa_id',
        'weekday',
        'start_time',
        'end_time',
        'classroom_id',
        'is_active',
        'position',
    ];

    protected $casts = [
        'weekday' => 'integer',
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function halaqa()
    {
        return $this->belongsTo(Halaqa::class, 'halaqa_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function seances()
    {
        return $this->hasMany(Seance::class, 'schedule_id');
    }
}

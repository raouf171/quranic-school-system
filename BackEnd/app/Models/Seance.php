<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    protected $table = 'seances' ; 
    protected $fillable = [
        'halaqa_id' , 'created_by','classroom_id' ,'date' , 'notes'
    ];

    protected $casts = [
        'date' => 'date',
    ];

//relations 

public function halaqat(){
    return $this->belongsTo(halaqa::class , 'halaqa_id') ; 
}

public function teacher(){
    return $this->belongsTo(Teacher::class , 'created_by') ; 
}

public function classroom(){
    return $this->belongsTo(Classroom::class , 'classroom_id') ; 
}

public function memorizations(){
    return $this->hasMany(Memorization::class , 'seance_id') ; 
}

public function revisions(){
    return $this->hasMany(Revision::class , 'seance_id') ; 
}

public function evaluations(){
    return $this->hasMany(Evaluation::class , 'seance_id') ;
}

public function attendances(){
    return $this->hasMany(Attendance::class , 'seance_id') ;
}

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Halaqa extends Model
{
    //

    protected $table = 'halaqat' ; 
    
    protected $fillable = [
        'teacher_id' , 'name' , 'schedule' , 'maxx_students' , 'is_active' 
    ];

    protected $casts = [
        'is_active' => 'boolean' , 
        'max_student'=> 'integer', 
    ];

    //relationships 
    public function teacher() {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    } 

    public function studensts() {
        return $this->hasMany(Student::class , 'halaqa_id') ;
    }

    public function seacnes(){
        return $this->hasMany(Seance::class , 'halaqa_id') ; 

    }

        public function isFull(): bool {
            if
             ($this->studensts->count() >= $this->max_students) {
                return true;
            } else {
                return false;
            }

        }
        

    
}

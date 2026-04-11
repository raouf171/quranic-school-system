<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
        

protected $table = 'teachers';

protected $fillable = [
        'account_id',
        'name',
        'hiring_date',
        'is_available',
    ];
    

 protected $casts = [
       
        'hiring_date'  => 'date',
        'is_available' => 'boolean',
    ];


    
    //relations

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function halaqat()
    {
        return $this->hasMany(Halaqa::class, 'teacher_id');
    }

    public function seances()
    {
        return $this->hasMany(Seance::class, 'created_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentProfile extends Model
{
    //
    protected $table  = 'parents' ;

    protected $fillable = [
        'account_id',
        'name',
        'phone',
        'occupation',
        'address',
    ];

    //relations

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
} ;



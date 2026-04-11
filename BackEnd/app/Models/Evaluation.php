<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $table ='evaluations' ; 

    protected $fillable = [
        'grade' , 'points' , 'description'
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    //relationships 
 public function revision()
    {
        return $this->belongsTo(Revision::class, 'revision_id');
    }

public function memorization()
    {
        return $this->belongsTo(Memorization::class, 'memorization_id');
    }
}

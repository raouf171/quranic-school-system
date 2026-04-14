<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';  

    protected $fillable = [
        'student_id' , 
        'month' , 
        'amount' , 
        'due_date'
        ,'paid_date'
        , 'status'
    ];
    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
        'status' => 'enum',
    ];

    //relations

    public function student(){
        return $this->belongsTo(Student::class , 'student_id') ; 
    }


    //helper methodes 

    public function isOverDue():bool{
return $this->status === 'pending'
            && $this->due_date->isPast();

    }

     public function markAsPaid(): void
    {
        $this->update([
            'status'    => 'paid',
            'paid_date' => today(),
        ]);
    }
}

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

    public function getNextSeance(): ?\App\Models\Seance
{
    return \App\Models\Seance::whereIn(
            'halaqa_id',
            $this->halaqat()->pluck('id')
        )
        ->whereHas('dateEntry', function ($q) {
            $q->whereDate('date_value', '>=', today());
        })
        ->with(['halaqa', 'classroom', 'dateEntry']) // use 'halaqa' if you rename relation
        ->orderBy(
            \App\Models\DateEntry::select('date_value')
                ->whereColumn('dates.id', 'seances.date_id')
                ->limit(1)
        )
        ->first();
}
}

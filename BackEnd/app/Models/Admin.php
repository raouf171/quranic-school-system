<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
      

protected $table = 'admins';


protected $fillable = [
        'account_id',
        'name',
        
    ];

    
        //relations 


// Admin creates many announce

 public function announcements()
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }
        // Admin belong to one account
     public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    
}

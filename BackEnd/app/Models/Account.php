<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens ; 

class Account extends Authenticatable {

use HasApiTokens ;

protected $table = 'accounts' ; 

protected $fillable = [ 
    'email' , 'password' , 'role' , 'is_active' , 
];

protected $hidden = [
    'password' , 'remeber_token' , 
];

protected $casts = [
    'is_active' => 'boolean' , 
    'password'  => 'hashed' , 
];


//les reltions 

//one account has one admin profile
public function admin(){
        return $this->hasOne(Admin::class, 'account_id');
    }

     // One Account ----->  One Teacher profile
   public function teacher()
    {
        return $this->hasOne(Teacher::class, 'account_id');
    }
     // One Account ----->  One prnt profile

    public function parentProfile(){
        return $this->hasOne(ParentProfile::class, 'account_id');

    }


    // hadi bch au lieu a chaque instruction ndiro if,,if,,if , auto assigned the role based on the account 
    public function getProfile()
    {
        return match($this->role) {
            'admin'   => $this->admin,
            'teacher' => $this->teacher,
            'parent'  => $this->parentProfile,
            default   => null,
        };



}
    //

}
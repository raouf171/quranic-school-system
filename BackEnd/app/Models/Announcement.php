<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'announcements';

    protected $fillable = [
        'title',
        'content',
        'created_by',
        'expiry_date',
        'target_roles'
    ];

   protected $casts = [
        'expiry_date' => 'date',
        'target_roles' => 'array',

    ];


    //relationshi^p
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    //helper 

    public function isExpired(): bool
    {
        return $this->expiry_date !== null
            && $this->expiry_date->isPast();
    }

    public function targetsRole(string $role): bool
    {
        return in_array('all', $this->target_roles)
            || in_array($role, $this->target_roles);
    }

}

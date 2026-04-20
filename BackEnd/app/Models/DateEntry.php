<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Nommé DateEntry car 'Date' est un mot réservé en PHP
// La table dans MySQL s'appelle bien 'dates'
class DateEntry extends Model
{
    // Laravel devinerait 'date_entries' → faux
    // On force le bon nom de table
    protected $table = 'dates';

    protected $fillable = [
        'date_value',
        'created_by',
        'label',
    ];

    protected $casts = [
        // Cast string → Carbon object
        // Permet: $dateEntry->date_value->format('Y-m-d')
        'date_value' => 'date',
    ];

    // ══════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════

    // DateEntry créée par un Teacher
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'created_by');
    }

    // Une DateEntry peut être liée à plusieurs séances
    public function seances()
    {
        return $this->hasMany(Seance::class, 'date_id');
    }
}
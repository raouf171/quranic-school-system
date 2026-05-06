<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Attendance;
use App\Models\Memorization;
use App\Models\Revision;
use App\Models\Seance;

class Ranking extends Model
{
    protected $table = 'rankings';

    // rankings utilise calculated_at pas created_at/updated_at
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'halaqa_id',
        'score',
        'rank',
        'period_type',
        'period_start',
        'period_end',
        'calculated_at',
    ];

    protected $casts = [
        'period_start'  => 'date',
        'period_end'    => 'date',
        'calculated_at' => 'datetime',
        'score'         => 'integer',
        'rank'          => 'integer',
    ];

    // ══════════════════════════════════════════
    // RELATIONSHIPS
    // ══════════════════════════════════════════

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function halaqa()
    {
        return $this->belongsTo(Halaqa::class, 'halaqa_id');
    }

    // ══════════════════════════════════════════
    // CALCUL DU SCORE
    // Appelé par les Observers après chaque save
    //
    // Formule:
    // score = Σ points (attendance) + Σ points (memorization) + Σ points (revision)
    // présent = +1 | absent = -1 | late/excused = 0
    // ══════════════════════════════════════════
    public static function calculateForStudent(
        int $studentId,
        int $halaqaId,
        string $start,
        string $end
    ): int {
        // Étape 1: IDs des séances dans cette halaqa pour cette période
        $seanceIds = Seance::where('seances.halaqa_id', $halaqaId)
            ->join('dates', 'seances.date_id', '=', 'dates.id')
            ->whereBetween('dates.date_value', [$start, $end])
            ->pluck('seances.id');

        if ($seanceIds->isEmpty()) {
            return 0;
        }

        // Étape 2: Points de présence (utilise la colonne 'points')
        $attendanceScore = Attendance::where('student_id', $studentId)
            ->whereIn('seance_id', $seanceIds)
            ->sum('points');

        // Étape 3: Points mémorisation (utilise la colonne 'points')
        $memScore = Memorization::where('student_id', $studentId)
            ->whereIn('seance_id', $seanceIds)
            ->sum('points');

        // Étape 4: Points révision (utilise la colonne 'points')
        $revScore = Revision::where('student_id', $studentId)
            ->whereIn('seance_id', $seanceIds)
            ->sum('points');

        return $attendanceScore + $memScore + $revScore;
    }
}
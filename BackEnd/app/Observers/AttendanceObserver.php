<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\Ranking;
use App\Models\Seance;
use App\Models\Teacher;use Carbon\Carbon;

class AttendanceObserver
{
    public function created(Attendance $attendance): void
    {
        $this->syncEvaluationPoints($attendance);
        $this->updateStudentScore($attendance);
    }

    public function updated(Attendance $attendance): void
    {
        $this->syncEvaluationPoints($attendance);
        $this->updateStudentScore($attendance);
    }

    public function deleted(Attendance $attendance): void
    {
        $this->updateStudentScore($attendance);
    }

    private function syncEvaluationPoints(Attendance $attendance): void
    {
        $points = match($attendance->status) {
            'present' => 1,
            'absent'  => 0,
            'excused' => 0,
            'late'    => 0,
            default   => 0,
        };

        $attendance->updateQuietly([
            'points' => $points, 
        ]);
    }

    private function updateStudentScore(Attendance $attendance): void
    {
        $seance = Seance::find($attendance->seance_id);
        if (!$seance) return;

        $studentId = $attendance->student_id;
        $halaqaId  = $seance->halaqa_id;

        // FIX: utiliser $seance->date en fallback si dateEntry null
        // évite le return prématuré qui bloque tout le calcul
        if ($seance->dateEntry && $seance->dateEntry->date_value) {
            $seanceDate = $seance->dateEntry->date_value;
        } elseif ($seance->date) {
            $seanceDate = $seance->date;
        } else {
            // Pas de date du tout → utiliser le mois courant
            $seanceDate = now();
        }

        $periodStart = Carbon::parse($seanceDate)->startOfMonth()->format('Y-m-d');
        $periodEnd   = Carbon::parse($seanceDate)
                                     ->endOfMonth()
                                     ->format('Y-m-d');

        $newScore = Ranking::calculateForStudent(
            $studentId,
            $halaqaId,
            $periodStart,
            $periodEnd
        );

        Ranking::updateOrCreate(
            [
                'student_id'   => $studentId,
                'halaqa_id'    => $halaqaId,
                'period_type'  => 'monthly',
                'period_start' => $periodStart,
                'period_end'   => $periodEnd,
            ],
            [
                'score'         => $newScore,
                'calculated_at' => now(),
            ]
        );
    }
}
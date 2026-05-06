<?php

namespace App\Observers;

use App\Models\Memorization;
use App\Models\Ranking;
use App\Models\Seance;

class MemorizationObserver
{
    public function created(Memorization $memorization): void
    {
        $this->syncPointsFromEvaluation($memorization);
        $this->updateStudentScore($memorization);
    }

    public function updated(Memorization $memorization): void
    {
        $this->syncPointsFromEvaluation($memorization);
        $this->updateStudentScore($memorization);
    }

    public function deleted(Memorization $memorization): void
    {
        $this->updateStudentScore($memorization);
    }

    // ══════════════════════════════════════════
    // MÉTHODES PRIVÉES
    // ══════════════════════════════════════════

    // Synchroniser grade et points depuis la table evaluations
    private function syncPointsFromEvaluation(Memorization $memorization): void
    {
        // Si evaluation_id fourni ET points pas encore défini
        if ($memorization->evaluation_id && $memorization->points == 0) {
            $evaluation = \App\Models\Evaluation::find($memorization->evaluation_id);

            if ($evaluation) {
                $memorization->updateQuietly([
                    'evaluation_grade' => $evaluation->grade,
                    'points'           => $evaluation->points,  // ← Changé: evaluation_points → points
                ]);
            }
        }
    }

    private function updateStudentScore(Memorization $memorization): void
    {
        $seance = Seance::find($memorization->seance_id);
        if (!$seance) return;

        // 🔧 FIX: Utiliser la date de la séance
        $seanceDate = $seance->dateEntry?->date_value;
        if (!$seanceDate) return;

        $periodStart = $seanceDate->copy()->startOfMonth()->format('Y-m-d');
        $periodEnd   = $seanceDate->copy()->endOfMonth()->format('Y-m-d');

        $newScore = Ranking::calculateForStudent(
            $memorization->student_id,
            $seance->halaqa_id,
            $periodStart,
            $periodEnd
        );

        Ranking::updateOrCreate(
            [
                'student_id'   => $memorization->student_id,
                'halaqa_id'    => $seance->halaqa_id,
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
<?php

namespace App\Observers;

use App\Models\Revision;
use App\Models\Ranking;
use App\Models\Seance;

// Exactement le même rôle que MemorizationObserver
// mais pour les révisions (muraja'ah)
class RevisionObserver
{
    // Appelé automatiquement après chaque enregistrement de muraja'ah
    public function created(Revision $revision): void
    {
        $this->syncEvaluationFromId($revision);
        $this->updateStudentScore($revision);
    }

    public function updated(Revision $revision): void
    {
        $this->syncEvaluationFromId($revision);
        $this->updateStudentScore($revision);
    }

    public function deleted(Revision $revision): void
    {
        $this->updateStudentScore($revision);
    }

    // ══════════════════════════════════════════
    // MÉTHODES PRIVÉES
    // ══════════════════════════════════════════

    // Synchroniser grade et points depuis la table evaluations
    // si evaluation_id fourni mais grade pas encore défini
    private function syncEvaluationFromId(Revision $revision): void
    {
        if ($revision->evaluation_id && !$revision->evaluation_grade) {
            $evaluation = \App\Models\Evaluation::find($revision->evaluation_id);

            if ($evaluation) {
                // 🔧 FIX 1: Use 'points' not 'evaluation_points'
                $revision->updateQuietly([
                    'evaluation_grade' => $evaluation->grade,
                    'points'           => $evaluation->points,  // ← Changed
                ]);
            }
        }
    }

    // Recalculer le score ranking du student
    // après chaque modification de révision
    private function updateStudentScore(Revision $revision): void
    {
        $seance = Seance::find($revision->seance_id);
        if (!$seance) return;

        // 🔧 FIX 2: Use seance date, not now()
        $seanceDate = $seance->dateEntry?->date_value;
        if (!$seanceDate) return;

        $periodStart = $seanceDate->copy()->startOfMonth()->format('Y-m-d');
        $periodEnd   = $seanceDate->copy()->endOfMonth()->format('Y-m-d');

        $newScore = Ranking::calculateForStudent(
            $revision->student_id,
            $seance->halaqa_id,
            $periodStart,
            $periodEnd
        );

        Ranking::updateOrCreate(
            [
                'student_id'   => $revision->student_id,
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
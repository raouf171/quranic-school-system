<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Student;

class PaymentObserver
{
    
    // Synchronise fee_status du Student automatiquement
    public function updated(Payment $payment): void
    {
        $this->syncStudentFeeStatus($payment);
    }

    public function created(Payment $payment): void
    {
        $this->syncStudentFeeStatus($payment);
    }


    private function syncStudentFeeStatus(Payment $payment): void
    {
        $student = Student::find($payment->student_id);
        if (!$student) return;

        // Si étudiant déjà exempt → ne pas changer
        // L'exemption est manuelle — l'Observer ne la touche pas
        if ($student->fee_status === 'exempt') {
            return;
        }

        // Trouver le paiement du mois courant
        $currentMonth = now()->format('Y-m');
        $currentPayment = Payment::where('student_id', $student->id)
                                 ->where('month', $currentMonth)
                                 ->first();

        if (!$currentPayment) {
            return;
        }

        
        $student->updateQuietly([
            'fee_status' => $currentPayment->status,
        ]);
    }
}
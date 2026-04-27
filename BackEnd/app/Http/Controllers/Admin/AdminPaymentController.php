<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Http\Requests\Admin\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AdminPaymentController extends Controller
{
    // GET /api/admin/payments
    // GET /api/admin/payments?status=pending
    // GET /api/admin/payments?student_id=1
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with('student')
                        ->latest('due_date');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        return $this->apiSuccess(
            PaymentResource::collection($query->paginate(20))
        );
    }

    // POST /api/admin/payments
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $exists = Payment::where('student_id', $request->student_id)
                         ->where('month', $request->month)
                         ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'يوجد سجل دفع لهذا الطالب في هذا الشهر',
            ], 422);
        }

        $payment = Payment::create($request->validated())->load('student');

        return $this->apiSuccess(
            new PaymentResource($payment),
            'تم إنشاء سجل الدفع',
            201
        );
    }

    // GET /api/admin/payments/{payment}
    public function show(Payment $payment): JsonResponse
    {
        return $this->apiSuccess(
            new PaymentResource($payment->load('student'))
        );
    }

    // PUT /api/admin/payments/{payment}
    // PaymentObserver se déclenche → student.fee_status mis à jour
    public function update(
        UpdatePaymentRequest $request,
        Payment $payment
    ): JsonResponse {
        $validated = $request->validated();

        $data = [
            'status' => $validated['status'],
        ];

        if (array_key_exists('amount', $validated)) {
            $data['amount'] = $validated['amount'];
        }

        if ($validated['status'] === 'paid') {
            $data['paid_date'] = Arr::get($validated, 'paid_date', today()->format('Y-m-d'));
        } else {
            $data['paid_date'] = Arr::get($validated, 'paid_date');
        }

        $payment->update($data);

        return $this->apiSuccess(
            new PaymentResource($payment->fresh('student')),
            'تم تحديث حالة الدفع'
        );
    }

    // GET /api/admin/students/{student}/payments
    public function studentPayments(Student $student): JsonResponse
    {
        $payments = Payment::where('student_id', $student->id)
                           ->orderBy('month', 'desc')
                           ->get();

        return $this->apiSuccess([
            'student'  => [
                'id'         => $student->id,
                'full_name'  => $student->full_name,
                'fee_status' => $student->fee_status,
            ],
            'payments' => PaymentResource::collection($payments)->resolve(),
        ]);
    }
}
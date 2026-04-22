<?php

namespace App\Http\Controllers\Admin;

use App\Factories\UserFactory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreParentRequest;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Http\Resources\ParentResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;

class AdminAccountController extends Controller
{
    // POST /api/admin/accounts/teacher
    public function storeTeacher(StoreTeacherRequest $request): JsonResponse
    {
        $result = UserFactory::createTeacher($request->validated());

        return response()->json([
            'message' => 'تم إنشاء حساب المعلم بنجاح',
            'account' => [
                'id'    => $result['account']->id,
                'email' => $result['account']->email,
                'role'  => $result['account']->role,
            ],
            'teacher' => new TeacherResource($result['teacher']),
        ], 201);
    }

    // POST /api/admin/accounts/parent
    public function storeParent(StoreParentRequest $request): JsonResponse
    {
        $result = UserFactory::createParent($request->validated());

        return response()->json([
            'message' => 'تم إنشاء حساب ولي الأمر بنجاح',
            'account' => [
                'id'    => $result['account']->id,
                'email' => $result['account']->email,
                'role'  => $result['account']->role,
            ],
            'parent' => new ParentResource($result['parent']),
        ], 201);
    }

    // GET /api/admin/accounts
    public function index(): JsonResponse
    {
        $accounts = Account::with(['admin', 'teacher', 'parentProfile'])
                           ->latest()
                           ->paginate(15);

        return response()->json($accounts);
    }

    // PUT /api/admin/accounts/{account}/toggle
    public function toggleActive(Account $account): JsonResponse
    {
        $account->update(['is_active' => !$account->is_active]);

        return response()->json([
            'message'   => $account->is_active ? 'تم تفعيل الحساب' : 'تم تعطيل الحساب',
            'is_active' => $account->is_active,
        ]);
    }
}
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

        return $this->apiSuccess([
            'account' => [
                'id'    => $result['account']->id,
                'email' => $result['account']->email,
                'role'  => $result['account']->role,
            ],
            'teacher' => new TeacherResource($result['teacher']),
        ], 'تم إنشاء حساب المعلم بنجاح', 201);
    }

    // POST /api/admin/accounts/parent
    public function storeParent(StoreParentRequest $request): JsonResponse
    {
        $result = UserFactory::createParent($request->validated());

        return $this->apiSuccess([
            'account' => [
                'id'    => $result['account']->id,
                'email' => $result['account']->email,
                'role'  => $result['account']->role,
            ],
            'parent' => new ParentResource($result['parent']),
        ], 'تم إنشاء حساب ولي الأمر بنجاح', 201);
    }

    // GET /api/admin/accounts
    public function index(): JsonResponse
    {
        $accounts = Account::with(['admin', 'teacher', 'parentProfile'])
                           ->latest()
                           ->paginate(15);

        return $this->apiSuccess(
            $accounts->items(),
            null,
            200,
            [
                'current_page' => $accounts->currentPage(),
                'last_page' => $accounts->lastPage(),
                'per_page' => $accounts->perPage(),
                'total' => $accounts->total(),
            ]
        );
    }

    // PUT /api/admin/accounts/{account}/toggle
    public function toggleActive(Account $account): JsonResponse
    {
        $account->update(['is_active' => !$account->is_active]);

        return $this->apiSuccess([
            'is_active' => $account->is_active,
        ], $account->is_active ? 'تم تفعيل الحساب' : 'تم تعطيل الحساب');
    }
}
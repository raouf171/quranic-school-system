<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateParentRequest;
use App\Http\Resources\ParentResource;
use App\Models\ParentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminParentController extends Controller
{
    // GET /api/admin/parents
    // GET /api/admin/parents?search=ahmed
    // GET /api/admin/parents?page=2
    public function index(Request $request): JsonResponse
    {
        $query = ParentProfile::with(['students', 'account'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where('name', 'LIKE', '%'.$search.'%');
        }

        $parents = $query->paginate(15);

        return $this->apiSuccess(ParentResource::collection($parents));
    }

    // GET /api/admin/parents/{parent}
    public function show(ParentProfile $parent): JsonResponse
    {
        $parent->load(['students', 'account']);

        return $this->apiSuccess(new ParentResource($parent));
    }

    // PUT /api/admin/parents/{parent}
    public function update(UpdateParentRequest $request, ParentProfile $parent): JsonResponse
    {
        $parent->update($request->validated());

        return $this->apiSuccess(new ParentResource($parent->fresh()));
    }
}
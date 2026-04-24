<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateParentRequest;
use App\Http\Resources\ParentResource;
use App\Models\ParentProfile;
use Illuminate\Http\JsonResponse;

class AdminParentController extends Controller
{
    // PUT /api/admin/parents/{parent}
    public function update(UpdateParentRequest $request, ParentProfile $parent): JsonResponse
    {
        $parent->update($request->validated());

        return $this->apiSuccess(new ParentResource($parent->fresh()));
    }
}

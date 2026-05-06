<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHalaqaRequest;
use App\Http\Requests\Admin\UpdateHalaqaRequest;
use App\Http\Resources\HalaqaResource;
use App\Models\Halaqa;
use App\Repositories\Interfaces\HalaqaRepositoryInterface;
use Illuminate\Http\JsonResponse;

class AdminHalaqaController extends Controller
{
    public function __construct(
        private HalaqaRepositoryInterface $halaqaRepository
    ) {}

    // GET /api/admin/halaqat
    public function index(): JsonResponse
    {
        $halaqat = $this->halaqaRepository->getAll();
        return $this->apiSuccess(
            HalaqaResource::collection($halaqat)
        );
    }

    // POST /api/admin/halaqat
    public function store(StoreHalaqaRequest $request): JsonResponse
    {
        $halaqa = $this->halaqaRepository->create(
            $request->validated()
        );

        return $this->apiSuccess(
            new HalaqaResource($halaqa),
            null,
            201
        );
    }

    // GET /api/admin/halaqat/{halaqa}
    public function show(Halaqa $halaqa): JsonResponse
    {
        $halaqa = $this->halaqaRepository->findById($halaqa->id);

        return $this->apiSuccess(new HalaqaResource($halaqa));
    }

    // PUT /api/admin/halaqat/{halaqa}
    public function update(
        UpdateHalaqaRequest $request,
        Halaqa $halaqa
    ): JsonResponse {
        $updated = $this->halaqaRepository->update(
            $halaqa,
            $request->validated()
        );

        return $this->apiSuccess(new HalaqaResource($updated));
    }

    // DELETE /api/admin/halaqat/{halaqa}
    // On désactive — on ne supprime pas (données historiques)
    public function destroy(Halaqa $halaqa): JsonResponse
    {
        $this->halaqaRepository->deactivate($halaqa);

        return $this->apiSuccess(
            null,
            'تم تعطيل الحلقة بنجاح'
        );
    }

    // GET /api/admin/halaqat/{halaqa}/students
    // Liste des étudiants dans une halaqa spécifique
    public function students(Halaqa $halaqa): JsonResponse
    {
        $students = $this->halaqaRepository
                         ->findByTeacher($halaqa->teacher_id ?? 0);

        // Utiliser StudentResource depuis le namespace global
        return $this->apiSuccess(
            \App\Http\Resources\StudentResource::collection(
                $halaqa->students()->with('parent')->get()
            )
        );
    }
}
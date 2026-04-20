<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminStudentController extends Controller
{

    public function __construct(
        private StudentRepositoryInterface $studentRepository
    ) {}

    // GET /api/admin/students
    // GET /api/admin/students?search=ahmed
    // GET /api/admin/students?page=2
    public function index(Request $request): JsonResponse
    {
        // Si paramètre search présent → recherche
        if ($request->has('search') && $request->search) {
            $students = $this->studentRepository->search($request->search);
            return response()->json(
                StudentResource::collection($students)
            );
        }

        // Sinon → liste paginée
        $students = $this->studentRepository->getAll(15);
        return response()->json(
            StudentResource::collection($students)
        );
    }

    // POST /api/admin/students
    // Créer un nouvel étudiant
    public function store(StoreStudentRequest $request): JsonResponse
    {
        // $request->validated() = seulement les champs validés
        // Jamais $request->all() dans un store → sécurité
        $student = $this->studentRepository->create(
            $request->validated()
        );

        // 201 = Created (pas 200)
        return response()->json(
            new StudentResource($student),
            201
        );
    }

    // GET /api/admin/students/{student}
   
    public function show(Student $student): JsonResponse
    {
        // Recharger avec toutes les relations pour la vue détail
        $student = $this->studentRepository->findById($student->id);

        if (!$student) {
            return response()->json([
                'message' => 'الطالب غير موجود',
            ], 404);
        }

        return response()->json(new StudentResource($student));
    }

    // PUT /api/admin/students/{student}
    public function update(
        UpdateStudentRequest $request,
        Student $student
    ): JsonResponse {
        $updated = $this->studentRepository->update(
            $student,
            $request->validated()
        );

        return response()->json(new StudentResource($updated));
    }

    // DELETE /api/admin/students/{student}
    public function destroy(Student $student): JsonResponse
    {
        $this->studentRepository->delete($student);

        return response()->json([
            'message' => 'تم حذف الطالب بنجاح',
        ], 200);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Http\Requests\Admin\UploadStudentPhotoRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminStudentController extends Controller
{

    public function __construct(
        private StudentRepositoryInterface $studentRepository
    ) {}

    // GET /api/admin/students/form-enums
    public function formEnums(): JsonResponse
    {
        return $this->apiSuccess([
            'gender' => [
                ['value' => 'male', 'label' => 'Garçon / ذكر'],
                ['value' => 'female', 'label' => 'Fille / أنثى'],
            ],
            'relationship_nature' => [
                ['value' => 'mother', 'label' => 'Mère'],
                ['value' => 'father', 'label' => 'Père'],
                ['value' => 'uncle', 'label' => 'Oncle'],
                ['value' => 'aunt', 'label' => 'Tante'],
                ['value' => 'grandfather', 'label' => 'Grand-père'],
                ['value' => 'grandmother', 'label' => 'Grand-mère'],
                ['value' => 'legal_guardian', 'label' => 'Tuteur légal'],
                ['value' => 'other', 'label' => 'Autre'],
            ],
            'school_level' => [
                ['value' => 'kindergarten', 'label' => 'Maternelle'],
                ['value' => 'primary', 'label' => 'Primaire'],
                ['value' => 'middle_cem', 'label' => 'CEM / Collège'],
                ['value' => 'high_school', 'label' => 'Lycée'],
                ['value' => 'university', 'label' => 'Université'],
                ['value' => 'other', 'label' => 'Autre'],
            ],
        ]);
    }

    // POST /api/admin/students/{student}/photo
    public function uploadPhoto(UploadStudentPhotoRequest $request, Student $student): JsonResponse
    {
        $file = $request->file('photo');
        $path = $file->store('students/photos', 'public');

        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $this->studentRepository->update($student, ['photo_path' => $path]);

        return $this->apiSuccess(
            new StudentResource($student->fresh(['halaqa', 'parent']))
        );
    }

    // DELETE /api/admin/students/{student}/photo
    public function deletePhoto(Student $student): JsonResponse
    {
        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
            $this->studentRepository->update($student, ['photo_path' => null]);
        }

        return $this->apiSuccess(
            new StudentResource($student->fresh(['halaqa', 'parent']))
        );
    }

    // GET /api/admin/students
    // GET /api/admin/students?search=ahmed
    // GET /api/admin/students?page=2
    public function index(Request $request): JsonResponse
    {
        // Si paramètre search présent → recherche
        if ($request->has('search') && $request->search) {
            $students = $this->studentRepository->search($request->search);
            return $this->apiSuccess(
                StudentResource::collection($students)
            );
        }

        // Sinon → liste paginée
        $students = $this->studentRepository->getAll(15);
        return $this->apiSuccess(
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
        return $this->apiSuccess(
            new StudentResource($student),
            null,
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

        return $this->apiSuccess(new StudentResource($student));
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

        return $this->apiSuccess(new StudentResource($updated));
    }

    // DELETE /api/admin/students/{student}
    public function destroy(Student $student): JsonResponse
    {
        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $this->studentRepository->delete($student);

        return $this->apiSuccess(
            null,
            'تم حذف الطالب بنجاح'
        );
    }
}
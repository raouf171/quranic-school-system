<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Http\Requests\Admin\UpdateAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Admin;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAnnouncementController extends Controller
{
    private function getAdmin(Request $request): Admin
    {
        return Admin::where('account_id', $request->user()->id)
                    ->firstOrFail();
    }

    // GET /api/admin/announcements
    public function index(): JsonResponse
    {
        $announcements = Announcement::with('admin')
                                     ->latest()
                                     ->paginate(15);

        return response()->json(
            AnnouncementResource::collection($announcements)
        );
    }

    // POST /api/admin/announcements
    public function store(
        StoreAnnouncementRequest $request
    ): JsonResponse {
        $admin = $this->getAdmin($request);

        $announcement = Announcement::create(array_merge(
            $request->validated(),
            ['created_by' => $admin->id]
        ));

        $announcement->load('admin');

        return response()->json(
            new AnnouncementResource($announcement),
            201
        );
    }

    // GET /api/admin/announcements/{announcement}
    public function show(Announcement $announcement): JsonResponse
    {
        return response()->json(
            new AnnouncementResource($announcement->load('admin'))
        );
    }

    // PUT /api/admin/announcements/{announcement}
    public function update(
        UpdateAnnouncementRequest $request,
        Announcement $announcement
    ): JsonResponse {
        $admin = $this->getAdmin($request);

        if ($announcement->created_by !== $admin->id) {
            return response()->json([
                'message' => 'لا يمكنك تعديل إعلان أنشأه مسؤول آخر',
            ], 403);
        }

        $announcement->update($request->validated());

        return response()->json(
            new AnnouncementResource($announcement->fresh(['admin']))
        );
    }

    // DELETE /api/admin/announcements/{announcement}
    public function destroy(Request $request, Announcement $announcement): JsonResponse
    {
        $admin = $this->getAdmin($request);

        if ($announcement->created_by !== $admin->id) {
            return response()->json([
                'message' => 'لا يمكنك حذف إعلان أنشأه مسؤول آخر',
            ], 403);
        }

        $announcement->delete();

        return response()->json([
            'message' => 'تم حذف الإعلان',
        ]);
    }
}
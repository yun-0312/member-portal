<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use App\Http\Requests\WorkshopStoreRequest;
use App\Http\Requests\WorkshopUpdateRequest;

class WorkshopController extends Controller
{
        public function index() {
        $workshops = Workshop::orderBy('start_at', 'desc')->get();

        $perPage = 20;
        $page = request()->input('page', 1);
        $offset = ($page - 1) * $perPage;

        $paginated = $workshops->slice($offset, $perPage)->values();

        $paginated->transform(function ($workshop) {
            $workshop->show_url = route('admin.workshops.show', $workshop->id);
            return $workshop;
        });

        return response()->json([
            'data' => $paginated,
            'store_url' => route('admin.workshops.store'),
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $workshops->count(),
            'last_page' => ceil($workshops->count() / $perPage),
        ]);
    }

    public function show(Workshop $workshop) {
        $this->authorize('view', $workshop);
        return response()->json([
            'workshop' => $workshop,
            'update_url' => route('admin.workshops.update', $workshop->id),
            'delete_url' => route('admin.workshops.destroy', $workshop->id),
            'index_url' => route('admin.workshops.index'),
        ]);
    }

    public function store(WorkshopStoreRequest $request) {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();

        $workshop = Workshop::create($validated);

        return response()->json([
            'message' => '研修会を登録しました',
            'workshop' => $workshop,
        ]);
    }

    public function update(WorkshopUpdateRequest $request, Workshop $workshop) {
        $validated = $request->validated();

        $workshop->update($validated);

        return response()->json([
            'message' => '研修会を更新しました',
            'workshop' => $workshop,
        ]);
    }

    public function destroy(Workshop $workshop) {
        $workshop->delete();

        return response()->json([
            'message' => '研修会を削除しました'
        ]);
    }
}

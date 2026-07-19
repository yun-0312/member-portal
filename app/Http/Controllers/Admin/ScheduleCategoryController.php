<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleCategory;
use App\Http\Requests\ScheduleCategoryStoreRequest;
use App\Http\Requests\ScheduleCategoryUpdateRequest;

class ScheduleCategoryController extends Controller
{
    public function index() {
        $categories = ScheduleCategory::orderBy('id')->get();

        $categories->transform(function ($category) {
            $category->show_url = route('admin.schedule-categories.show', $category->id);
            return $category;
        });

        return response()->json([
            'data' => $categories,
            'store_url' => route('admin.schedule-categories.store'),
        ]);
    }

    public function show(ScheduleCategory $category) {
        return response()->json([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'update_url' => route('admin.schedule-categories.update', $category->id),
                'destroy_url' => route('admin.schedule-categories.destroy', $category->id),
                'index_url' => route('admin.schedule-categories.index'),
            ],
        ]);
    }

    public function store(ScheduleCategoryStoreRequest $request) {
        $validated = $request->validated();

        $scheduleCategory = ScheduleCategory::create($validated);

        return response()->json([
            'message' => 'カテゴリを登録しました',
            'category' => $scheduleCategory,
        ]);
    }

    public function update(ScheduleCategoryUpdateRequest $request, ScheduleCategory $scheduleCategory) {
        $validated = $request->validated();

        $scheduleCategory->update($validated);

        return response()->json([
            'message' => 'カテゴリを更新しました',
            'category' => $scheduleCategory,
        ]);
    }

    public function destroy(ScheduleCategory $category) {
        if ($category->schedules()->exists()) {
            return response()->json([
                'message' => 'このカテゴリにはスケジュールが存在するため削除できません。',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'カテゴリを削除しました',
        ]);

    }
}

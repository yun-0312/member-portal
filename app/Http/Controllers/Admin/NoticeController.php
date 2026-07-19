<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\NoticeSearchTrait;
use App\Models\Notice;
use App\Http\Requests\NoticeStoreRequest;
use App\Http\Requests\NoticeUpdateRequest;

class NoticeController extends Controller
{
    use NoticeSearchTrait;

    public function index(Request $request) {
        $notices = $this->searchNotices($request);

        $notices->transform(function ($notice) {
            $notice->show_url = route('admin.notices.show', $notice->id);
            return $notice;
        });

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $paged = $notices->slice(($page -1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $paged,
            'store_url' =>route('admin.notices.store'),
            'total' => $notices->count(),
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($notices->count() / $perPage),
        ]);
    }

    public function show(Notice $notice) {
        $notice->load([
            'category',
            'files',
        ]);

        return [
            'id' => $notice->id,
            'title' => $notice->title,
            'body' => $notice->body,
            'date' => $notice->published_at?->format('Y-m-d'),
            'category' => [
                'id' => $notice->category->id,
                'name' => $notice->category->name,
                'slug' => $notice->category->slug,
            ],
            'files' => $notice->files->isNotEmpty()
                ? $notice->files->map(fn($file) => [
                'id' => $file->id,
                'name' => $file->name,
                'url' => $file->url,
            ]) : null,
            'index_url' => route('admin.notices.index'),
            'update_url' => route('admin.notices.update', $notice->id),
            'delete_url' => route('admin.notices.destroy', $notice->id),
        ];
    }

    public function store(NoticeStoreRequest $request) {
        $validated = $request->validated();

        $notice = Notice::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'お知らせを作成しました',
            'notice' => $notice,
        ], 201);
    }

    public function update(NoticeUpdateRequest $request, Notice $notice) {
        $validated = $request->validated();

        $notice->update($validated);

        return response()->json([
            'message' => 'お知らせを更新しました',
            'notice' => $notice,
        ]);
    }

    public function destroy(Notice $notice) {
        $notice->delete();

        return response()->json([
            'message' => 'お知らせを削除しました'
        ]);
    }
}

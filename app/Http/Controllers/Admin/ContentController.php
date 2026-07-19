<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\ContentSearchTrait;
use Illuminate\Http\Request;
use App\Models\Content;
use App\Http\Requests\ContentStoreRequest;
use App\Http\Requests\ContentUpdateRequest;

class ContentController extends Controller
{
    use ContentSearchTrait;

    public function index(Request $request) {
        $contents = $this->searchContents($request);

        $contents->transform(function ($content) {
            $content->show_url = route('admin.contents.show', $content->id);
            return $content;
        });

        $perPage = 10;
        $page = $request->input('page', 1);
        $paged = $contents->slice(($page -1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $paged,
            'store_url' => route('admin.contents.store'),
            'total' => $contents->count(),
            'current_page' => $page,
            'per_page' => $perPage,
        ]);
    }

    public function show(Content $content) {
        $this->authorize('view', $content);
        $content->load(['category', 'subcategory']);

        return response()->json([
            'content' => $content,
            'index_url' => route('admin.contents.index'),
            'update_url' => route('admin.contents.update', $content->id),
            'delete_url' => route('admin.contents.destroy', $content->id),
        ]);
    }

    public function store(ContentStoreRequest $request) {
        $validated = $request->validated();

        $content = Content::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'コンテンツを作成しました',
            'content' => $content,
        ], 201);
    }

    public function update(ContentUpdateRequest $request, Content $content) {
        $validated = $request->validated();

        $content->update($validated);

        return response()->json([
            'message' => 'コンテンツを更新しました',
            'content' => $content,
        ]);
    }

    public function destroy(Content $content) {
        $content->delete();

        return response()->json([
            'message' => 'コンテンツを削除しました'
        ]);
    }
}

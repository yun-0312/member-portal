<?php

namespace App\Http\Controllers;

use App\Traits\NoticeSearchTrait;
use Illuminate\Http\Request;
use App\Models\Notice;
use App\Traits\FiltersByPolicy;

class NoticeController extends Controller
{
    use FiltersByPolicy;
    use NoticeSearchTrait;

    public function index(Request $request) {
        $notices = $this->searchNotices($request);

        $filtered = $this->filterByPolicy($notices);

        $filtered->transform(function ($notice) {
            $notice->show_url = route('notices.show', $notice->id);
            return $notice;
        });

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $paged = $filtered->slice(($page -1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $paged,
            'total' => $filtered->count(),
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($filtered->count() / $perPage),
        ]);
    }

    public function show(Notice $notice) {
        $this->authorize('view', $notice);

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
            'index_url' => route('notices.index'),
        ];
    }
}

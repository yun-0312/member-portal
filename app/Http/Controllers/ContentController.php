<?php

namespace App\Http\Controllers;

use App\Traits\ContentSearchTrait;
use Illuminate\Http\Request;
use App\Models\Content;
use App\Traits\FiltersByPolicy;

class ContentController extends Controller
{
    use FiltersByPolicy;
    use ContentSearchTrait;

    public function index(Request $request) {
        $contents = $this->searchContents($request);

        $filtered = $this->filterByPolicy($contents);

        $filtered->transform(function ($content) {
            $content->show_url = route('contents.show', $content->id);
            return $content;
        });

        $perPage = 10;
        $page = $request->input('page', 1);
        $paged = $filtered->slice(($page -1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $paged,
            'total' => $filtered->count(),
            'current_page' => $page,
            'per_page' => $perPage,
        ]);
    }

    public function years() {
        return Content::selectRaw('YEAR(published_at) as year')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();
    }

    public function show(Content $content) {
        $this->authorize('view', $content);
        $content->load(['category', 'subcategory']);

        return response()->json([
            'content' => $content,
            'index_url' => route('contents.index'),
        ]);
    }
}

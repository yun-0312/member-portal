<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Models\Video;

trait VideoSearchTrait
{
    protected function searchVideos(Request $request, ?array $with = null)
    {
        $relations = $with ?? ['files'];

        $query = Video::query()
            ->with($relations);

        // 年度検索
        if ($request->year) {
            $query->whereYear('published_at', $request->year);
        }

        // キーワード検索（title / body）
        if ($request->keyword) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', "%{$keyword}%")
                    ->orWhere('body', 'LIKE', "%{$keyword}%");
            });
        }

        return $query;
    }
}

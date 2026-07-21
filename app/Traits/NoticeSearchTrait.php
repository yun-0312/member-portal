<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Models\Notice;

trait NoticeSearchTrait
{
    protected function searchNotices(Request $request, ?array $with = null)
    {
        $relations = $with ?? ['category', 'files'];

        $query = Notice::query()
            ->with($relations);

        // カテゴリ slug
        if ($request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // 日付（Y-m-d）
        if ($request->date) {
            $query->whereDate('published_at', $request->date);
        }

        // 年度検索（published_at の year）
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

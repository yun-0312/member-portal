<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Models\Content;

trait ContentSearchTrait
{
    protected function searchContents(Request $request)
    {
        $query = Content::query()
            ->with(['category', 'subcategory', 'files']);

        // カテゴリ slug、id
        if ($request->filled('category')) {
            $category = $request->category;
            $query->whereHas('category', function ($q) use ($category) {
                if (is_numeric($category)) {
                    $q->where('id', $category);
                } else {
                    $q->where('slug', $category);
                }
            });
        }

        // サブカテゴリ ID
        if ($request->subcategory) {
            $query->where('subcategory_id', $request->subcategory);
        }

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

<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Faq;

trait FaqSearchTrait
{
    protected function searchFaqs(Request $request)
    {
        $query = Faq::query()
            ->with('category')
            ->leftJoin('faq_categories', 'faqs.category_id', '=', 'faq_categories.id')
            ->orderBy('faq_categories.sort_order')
            ->orderBy('faqs.created_at')
            ->select('faqs.*');

        // 期間検索
        $start = $request->query('start_date');
        $end   = $request->query('end_date');
        $month = $request->query('month');

        if ($month) {
            try {
                $start = Carbon::parse($month . '-01')->startOfMonth()->toDateString();
                $end   = Carbon::parse($month . '-01')->endOfMonth()->toDateString();
            } catch (\Exception $e) {
                return ['error' => '月の形式が不正です（例: 2026-04）'];
            }
        }

        if ($start) {
            $query->whereDate('created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('created_at', '<=', $end);
        }

        // カテゴリID検索
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // カテゴリ名検索（日本語）
        if ($request->category_name) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->category_name . '%');
            });
        }

        // キーワード検索（質問・回答）
        if ($request->keyword) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('question', 'LIKE', "%{$keyword}%")
                    ->orWhere('answer', 'LIKE', "%{$keyword}%");
            });
        }

        return $query->get();
    }
}
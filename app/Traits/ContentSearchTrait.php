<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;

trait ContentSearchTrait
{
    /**
     * 全コンテンツ対応の共通検索ロジック
     */
    protected function applyContentSearch(Request $request, ?array $with = null)
    {
        // 1. Eager Load リレーションの決定
        $defaultRelations = property_exists($this, 'extraRelations') ? $this->extraRelations : [];
        $relations = $with ?? $defaultRelations;

        // 2. モデルインスタンスの生成
        $model = method_exists($this, 'newModel') ? $this->newModel() : new $this->modelClass;

        $query = !empty($relations)
            ? $model->newQuery()->with($relations)
            : $model->newQuery();

        // A. カテゴリ検索（ID、slug、リレーション名対応）
        if ($request->filled('category')) {
            $category = $request->category;
            $query->whereHas('category', function ($q) use ($category) {
                if (is_numeric($category)) {
                    $q->where('id', $category);
                } else {
                    // ★ slug カラムを外し、name のあいまい検索のみにする
                    $q->where('name', 'LIKE', "%{$category}%");
                }
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('subcategory')) {
            $query->where('subcategory_id', $request->subcategory);
        }

        // B. 日付・年度・期間検索（faqs / workshops / その他で判定カラムを切り替え）
        $tableName = $model->getTable();
        $dateColumn = match ($tableName) {
            'faqs'       => 'created_at',
            'workshops'  => 'start_at',
            default      => 'published_at',
        };

        if ($request->filled('year')) {
            $query->whereYear($dateColumn, $request->year);
        }
        if ($request->filled('date')) {
            $query->whereDate($dateColumn, $request->date);
        }

        // 期間指定（start_date / end_date / month）
        $start = $request->query('start_date');
        $end   = $request->query('end_date');
        if ($request->filled('month')) {
            try {
                $start = Carbon::parse($request->month . '-01')->startOfMonth()->toDateString();
                $end   = Carbon::parse($request->month . '-01')->endOfMonth()->toDateString();
            } catch (\Exception $e) {
                // 不正な日付フォーマットは無視
            }
        }
        if ($start) {
            $query->whereDate($dateColumn, '>=', $start);
        }
        if ($end) {
            $query->whereDate($dateColumn, '<=', $end);
        }

        // C. キーワード検索（テーブルごとに実際のマイグレーションに存在するカラムだけを指定）
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            // テーブルごとのキーワード検索対象カラムマップ
            $searchColumns = match ($tableName) {
                'faqs'      => ['question', 'answer'],
                'videos'    => ['title', 'description'],
                'workshops' => ['title', 'description', 'location', 'lecture'],
                'notices'   => ['title', 'committee_name', 'body'],
                'contents'  => ['title', 'body'],
                default     => ['title', 'body', 'description'],
            };

            $query->where(function ($q) use ($keyword, $searchColumns) {
                foreach ($searchColumns as $index => $column) {
                    if ($index === 0) {
                        $q->where($column, 'LIKE', "%{$keyword}%");
                    } else {
                        $q->orWhere($column, 'LIKE', "%{$keyword}%");
                    }
                }
            });
        }

        return $query;
    }
}
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

        $normalizeValues = function ($value): array {
            if ($value === null) {
                return [];
            }

            if (is_array($value)) {
                return array_values(array_filter($value, static fn ($item) => $item !== null && $item !== ''));
            }

            if (is_string($value)) {
                $trimmed = trim($value);
                if ($trimmed === '') {
                    return [];
                }

                return array_values(array_filter(array_map('trim', explode(',', $trimmed)), static fn ($item) => $item !== ''));
            }

            return [$value];
        };

        // A. カテゴリ検索（ID、slug、リレーション名対応）
        $categories = $normalizeValues($request->query('category'));
        if (!empty($categories)) {
            $query->where(function ($q) use ($categories) {
                foreach ($categories as $category) {
                    $q->orWhereHas('category', function ($sub) use ($category) {
                        if (is_numeric($category)) {
                            $sub->where('id', $category);
                        } else {
                            $sub->where('name', 'LIKE', "%{$category}%")
                                ->orWhere('slug', 'LIKE', "%{$category}%");
                        }
                    });
                }
            });
        }

        $categoryIds = $normalizeValues($request->query('category_id'));
        if (!empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        // -------------------------------------------------------------
        // ⭐ サブカテゴリ検索（ID、slug、リレーション名対応に修正）
        // -------------------------------------------------------------
        $subcategories = $normalizeValues($request->query('subcategory') ?? $request->query('sub_category'));
        if (!empty($subcategories)) {
            $query->where(function ($q) use ($subcategories) {
                foreach ($subcategories as $subcategory) {
                    $q->orWhereHas('subcategory', function ($sub) use ($subcategory) {
                        if (is_numeric($subcategory)) {
                            $sub->where('id', $subcategory);
                        } else {
                            $sub->where('name', 'LIKE', "%{$subcategory}%")
                                ->orWhere('slug', 'LIKE', "%{$subcategory}%");
                        }
                    });
                }
            });
        }

        $subcategoryIds = $normalizeValues($request->query('subcategory_id'));
        if (!empty($subcategoryIds)) {
            $query->whereIn('subcategory_id', $subcategoryIds);
        }

        // B. 日付・年度・期間検索
        $tableName = $model->getTable();
        $dateColumn = match ($tableName) {
            'faqs'       => 'created_at',
            'workshops'  => 'start_at',
            default      => 'published_at',
        };

        $years = $normalizeValues($request->query('year'));
        if (!empty($years)) {
            $query->where(function ($q) use ($years, $dateColumn) {
                foreach ($years as $year) {
                    $q->orWhereYear($dateColumn, $year);
                }
            });
        }

        $dates = $normalizeValues($request->query('date'));
        if (!empty($dates)) {
            $query->where(function ($q) use ($dates, $dateColumn) {
                foreach ($dates as $date) {
                    $q->orWhereDate($dateColumn, $date);
                }
            });
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

        // C. キーワード検索
        $keywords = $normalizeValues($request->query('keyword'));
        if (!empty($keywords)) {
            $query->where(function ($q) use ($keywords, $tableName) {
                $searchColumns = match ($tableName) {
                    'faqs'      => ['question', 'answer'],
                    'videos'    => ['title', 'description'],
                    'workshops' => ['title', 'description', 'location', 'lecture'],
                    'notices'   => ['title', 'committee_name', 'body'],
                    'contents'  => ['title', 'body'],
                    default     => ['title', 'body', 'description'],
                };

                foreach ($keywords as $keyword) {
                    $keyword = trim($keyword);
                    if ($keyword === '') {
                        continue;
                    }

                    $q->where(function ($sub) use ($keyword, $searchColumns) {
                        foreach ($searchColumns as $columnIndex => $column) {
                            if ($columnIndex === 0) {
                                $sub->where($column, 'LIKE', "%{$keyword}%");
                            } else {
                                $sub->orWhere($column, 'LIKE', "%{$keyword}%");
                            }
                        }
                    });
                }
            });
        }

        // D. ID検索
        $ids = $normalizeValues($request->query('id'));
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        return $query;
    }
}
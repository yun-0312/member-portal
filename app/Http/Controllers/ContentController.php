<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\BasePublicContentController;
use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\ContentSubcategory;

class ContentController extends BasePublicContentController
{
    protected array $indexExtraRelations = ['category', 'subcategory', 'roles', 'files'];

    protected string $modelClass = Content::class;
    protected string $routePrefix = 'contents';

    //表示条件分岐のためオーバーライド
    public function index(Request $request) {
        $categoryParam = $request->input('category_id') ?? $request->input('category');
        $subcategoryParam = $request->input('subcategory_id') ?? $request->input('sub_category') ?? $request->input('subcategory');
        $year = $request->input('year');

        // 1. パラメータが slug (文字列) の場合、DBから Model を特定
        $category = null;
        if (!empty($categoryParam)) {
            $category = is_numeric($categoryParam)
                ? ContentCategory::with('subcategories')->find($categoryParam)
                : ContentCategory::with('subcategories')->where('slug', $categoryParam)->first();

            // Trait側で文字列のまま検索条件が作られないよう、リクエストのパラメータをIDで上書き
            if ($category) {
                $request->merge(['category_id' => $category->id]);
            }
        }

        $subcategory = null;
        if (!empty($subcategoryParam)) {
            $subcategory = is_numeric($subcategoryParam)
                ? ContentSubcategory::find($subcategoryParam)
                : ContentSubcategory::where('slug', $subcategoryParam)->first();

            // リクエストのパラメータをIDで上書き
            if ($subcategory) {
                $request->merge(['subcategory_id' => $subcategory->id]);
            }
        }

        // 2. IDに正規化したリクエストで search() を実行
        $query = $this->search($request);

        // 3. 年度 (year) が指定されている場合 -> 通常の記事一覧を出力
        if (!empty($year)) {
            return response()->json([
                'display_mode' => 'list',
                'contents'     => $this->fetchPaginatedContents($query, $request),
            ]);
        }

        // 4. SubCategory が指定されている場合
        if ($subcategory) {
            if ($subcategory->display_type === 'year_archive') {
                $years = (clone $query)
                    ->reorder()
                    ->whereNotNull($this->publishedDateColumn)
                    ->selectRaw('YEAR(' . $this->publishedDateColumn . ') as year')
                    ->groupBy('year')
                    ->orderBy('year', 'desc')
                    ->pluck('year')
                    ->map(fn($y) => (int)$y)
                    ->values();

                return response()->json([
                    'display_mode' => 'year_archive',
                    'context'      => [
                        'type' => 'subcategory',
                        'id'   => $subcategory->id,
                        'name' => $subcategory->name,
                        'slug' => $subcategory->slug,
                    ],
                    'years'        => $years,
                ]);
            }

            if ($subcategory->display_type === 'children' && $subcategory->children()->exists()) {
                return response()->json([
                    'display_mode'  => 'subcategory',
                    'context'       => [
                        'type' => 'subcategory',
                        'id'   => $subcategory->id,
                        'name' => $subcategory->name,
                        'slug' => $subcategory->slug,
                    ],
                    'subcategories' => $subcategory->children()->orderBy('sort_order')->get(),
                ]);
            }

            return response()->json([
                'display_mode' => 'list',
                'contents'     => $this->fetchPaginatedContents($query, $request),
            ]);
        }

        // 5. Category が指定されている場合
        if ($category) {
            if ($category->display_type === 'year_archive') {
                $years = (clone $query)
                    ->reorder()
                    ->whereNotNull($this->publishedDateColumn)
                    ->selectRaw('YEAR(' . $this->publishedDateColumn . ') as year')
                    ->groupBy('year')
                    ->orderBy('year', 'desc')
                    ->pluck('year')
                    ->map(fn($y) => (int)$y)
                    ->values();

                return response()->json([
                    'display_mode' => 'year_archive',
                    'context'      => [
                        'type' => 'category',
                        'id'   => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ],
                    'years'        => $years,
                ]);
            }

            if ($category->display_type === 'subcategory' || $category->subcategories->isNotEmpty()) {
                return response()->json([
                    'display_mode'  => 'subcategory',
                    'context'       => [
                        'type' => 'category',
                        'id'   => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ],
                    'subcategories' => $category->subcategories()->orderBy('sort_order')->get(),
                ]);
            }

            return response()->json([
                'display_mode' => 'list',
                'contents'     => $this->fetchPaginatedContents($query, $request),
            ]);
        }

        // 6. パラメータ指定がない場合（全件一覧）
        return response()->json([
            'display_mode' => 'list',
            'contents'     => $this->fetchPaginatedContents($query, $request),
        ]);
    }

    private function fetchPaginatedContents($query, Request $request) {
        return $query
            ->when(!empty($this->indexExtraRelations), fn($q) => $q->with($this->indexExtraRelations))
            ->visibleTo($request->user())
            ->latest($this->publishedDateColumn)
            ->paginate(15);
    }

    public function years() {
        return Content::selectRaw('YEAR(published_at) as year')
            ->whereNotNull('published_at')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(fn($y) => (int)$y);
    }

}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminContentController;
use Illuminate\Http\Request;
use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\ContentSubcategory;
use App\Http\Requests\ContentStoreRequest;
use App\Http\Requests\ContentUpdateRequest;

class ContentController extends BaseAdminContentController
{
    protected array $indexExtraRelations = ['category', 'subcategory', 'roles', 'files'];
    protected array $showExtraRelations = ['category', 'subcategory','files', 'creator', 'roles'];
    protected string $modelClass = Content::class;
    protected string $routePrefix = 'contents';

    protected string $storeRequestClass = ContentStoreRequest::class;
    protected string $updateRequestClass = ContentUpdateRequest::class;

        //表示条件分岐のためオーバーライド
    public function index(Request $request) {
        $this->authorize('viewAny', $this->modelClass);

        $categoryParam = $request->input('category_id') ?? $request->input('category');
        $subcategoryParam = $request->input('subcategory_id') ?? $request->input('sub_category') ?? $request->input('subcategory');
        $year = $request->input('year');

        // 1. パラメータが slug (文字列) の場合、DBから Model を特定
        $category = null;
        if (!empty($categoryParam)) {
            $category = is_numeric($categoryParam)
                ? ContentCategory::with('topLevelSubcategories')->find($categoryParam)
                : ContentCategory::with('topLevelSubcategories')->where('slug', $categoryParam)->first();

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
                'store_url' => '/admin/contents',
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
                    'store_url' => '/admin/contents',
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
                    'store_url' => '/admin/contents',
                    'context'       => [
                        'type' => 'subcategory',
                        'id'   => $subcategory->id,
                        'name' => $subcategory->name,
                        'slug' => $subcategory->slug,
                    ],
                    'subcategories' => $subcategory->children()->orderBy('sort_order')->get(),
                ]);
            }

            // list モード
            return response()->json([
                'display_mode' => 'list',
                'store_url'    => '/admin/contents/store',
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
                    'store_url' => '/admin/contents/',
                    'context'      => [
                        'type' => 'category',
                        'id'   => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ],
                    'years'        => $years,
                ]);
            }

            if ($category->display_type === 'subcategory' || $category->topLevelSubcategories->isNotEmpty()) {
                return response()->json([
                    'display_mode'  => 'subcategory',
                    'store_url' => '/admin/contents',
                    'context'       => [
                        'type' => 'category',
                        'id'   => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ],
                    'subcategories' => $category->topLevelSubcategories()->orderBy('sort_order')->get(),
                ]);
            }

            // list モード
            return response()->json([
                'display_mode' => 'list',
                'store_url'    => '/admin/contents/',
                'contents'     => $this->fetchPaginatedContents($query, $request),
            ]);
        }

        // 6. パラメータ指定がない場合（全件一覧）
        return response()->json([
            'display_mode' => 'list',
            'store_url' => '/admin/contents',
            'contents'     => $this->fetchPaginatedContents($query, $request),
        ]);
    }

    private function fetchPaginatedContents($query, Request $request) {
        $paginator = $query
            ->when(!empty($this->indexExtraRelations), fn($q) => $q->with($this->indexExtraRelations))
            ->latest($this->publishedDateColumn)
            ->paginate(15);

        return $paginator->through(function ($content) {
            $content->show_url = "/admin/contents/{$content->id}";
            return $content;
        });
    }

    //Url追加のためオーバーライド
    public function show($id) {
        $item = $this->findModel($id);
        $this->authorize('view', $item);

        if (!empty($this->showExtraRelations)) {
            $item->load($this->showExtraRelations);
        }

        return response()->json([
            'item' => $item,
            'index_url' => "/admin/{$this->routePrefix}?category={$item->category->slug}",
            'update_url' => "/admin/{$this->routePrefix}/{$item->id}",
            'delete_url' => "/admin/{$this->routePrefix}/{$item->id}",
            'role_targetable_url' => "/admin/{$this->routePrefix}/{$item->id}/roles",
            'roles' => $item->roles->map(function ($role) use ($item) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'destroy_url' => "/admin/{$this->routePrefix}/{$item->id}/roles/{$role->id}",
                ];
            }),
        ]);
    }

}

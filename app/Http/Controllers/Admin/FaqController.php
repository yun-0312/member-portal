<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminContentController;
use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Http\Requests\FaqStoreRequest;
use App\Http\Requests\FaqUpdateRequest;
use Carbon\Carbon;

class FaqController extends BaseAdminContentController
{
    protected string $modelClass = Faq::class;
    protected string $routePrefix = 'faqs';

    protected string $storeRequestClass = FaqStoreRequest::class;
    protected string $updateRequestClass = FaqUpdateRequest::class;

    protected array $indexExtraRelations = ['category'];
    protected array $showExtraRelations = ['category', 'creator'];

    protected function search(Request $request) {
        // 共通検索Traitを実行
        $query = $this->applyContentSearch($request);

        // FAQ特有の「カテゴリの表示順 ➔ 作成日順」ソート
        return $query->leftJoin('faq_categories', 'faqs.category_id', '=', 'faq_categories.id')
            ->orderBy('faq_categories.sort_order')
            ->orderBy('faqs.created_at')
            ->select('faqs.*');
    }

    //URL追加のためオーバーライド
    public function index(Request $request) {
        $this->authorize('viewAny', $this->modelClass);
        $query = $this->search($request);

        $items = $query
            ->when(!empty($this->indexExtraRelations), fn($q) => $q->with($this->indexExtraRelations))
            ->paginate(15)
            ->through(function ($item) {
                $item->show_url = route("admin.{$this->routePrefix}.show", $item->id);
                return $item;
            })
            ->toArray();

        $items['store_url'] = route("admin.{$this->routePrefix}.store");
        $items['export_url'] = route("{$this->routePrefix}.export") . '?' . http_build_query($request->query());
        $items['import_url'] = route("admin.{$this->routePrefix}.import");

        return response()->json($items);
    }

    public function import(Request $request) {
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);

        $errors = [];
        $successCount = 0;
        $lineNumber = 1;

        while(($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            //文字コードを自動判定して UTF-8 に変換
            $row = array_map(function ($value) {
                // BOM除去
                $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);

                // 文字コード判定
                $encoding = mb_detect_encoding($value, ['UTF-8', 'SJIS-win', 'CP932'], true);

                // UTF-8 に変換
                return mb_convert_encoding($value, 'UTF-8', $encoding ?: 'SJIS-win');
            }, $row);

            //日付
            $dateString = trim($row[0], "\" \t\n\r\0\x0B");
            $dateString = str_replace('/', '-', $dateString);

            try {
                $createdAt = Carbon::parse($dateString);
            } catch (\Exception $e) {
                $errors[] = [
                    'line' => $lineNumber,
                    'reason' => '受付日が不正です:' . $row[0],
                ];
                continue;
            }

            //category_id
            $categoryId = $row[1];
            if (!FaqCategory::where('id', $categoryId)->exists()) {
                $errors[] = [
                    'line' => $lineNumber,
                    'reason' => '診療区分Noが存在しません:' . $categoryId,
                ];
                continue;
            }

            //answer
            if (empty($row[3])) {
                $errors[] = [
                    'line' => $lineNumber,
                    'reason' => '質問内容が空です',
                ];
                continue;
            }

            //question
            if (empty($row[4])) {
                $errors[] = [
                    'line' => $lineNumber,
                    'reason' => '回答内容が空です',
                ];
                continue;
            }

            $faq = Faq::create([
                'question' => $row[3],
                'answer' => $row[4],
                'category_id' => $categoryId,
                'created_by' => auth()->id(),
            ]);

            $faq->created_at = $createdAt;
            $faq->save();

            $successCount++;
        }
        return response()->json([
            'message' => 'FAQをインポートしました',
            'success_count' => $successCount,
            'error_count' => count($errors),
            'errors' => $errors,
        ]);
    }
}

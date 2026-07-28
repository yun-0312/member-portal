<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminContentController;
use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Http\Requests\FaqStoreRequest;
use App\Http\Requests\FaqUpdateRequest;
use Illuminate\Support\Facades\DB;
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
            ->orderBy('faqs.received_at')
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
                $item->update_url = "/admin/{$this->routePrefix}/{$item->id}";
                $item->delete_url = "/admin/{$this->routePrefix}/{$item->id}";
                return $item;
            })
            ->toArray();

        $items['store_url'] = "/admin/{$this->routePrefix}";
        $items['export_url'] = "/{$this->routePrefix}/export" . '?' . http_build_query($request->query());
        $items['import_url'] = "/admin/{$this->routePrefix}/import";

        return response()->json($items);
    }

    public function import(Request $request) {
        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return response()->json([
                'message' => 'ファイルが正しくアップロードされませんでした。',
                'success_count' => 0,
                'error_count' => 1,
                'errors' => [['line' => 0, 'reason' => 'ファイルが無効です']]
            ], 400);
        }

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return response()->json([
                'message' => 'ファイルを開けませんでした。'
            ], 500);
        }

        $header = fgetcsv($handle);

        $errors = [];
        $validRows = [];
        $lineNumber = 1;

        // Phase 1: CSV全行のバリデーション・チェック
        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            // 空行は無視
            if (empty(array_filter($row))) {
                continue;
            }

            // 文字コードを UTF-8 に変換
            $row = array_map(function ($value) {
                if ($value === null) return '';
                $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
                $encoding = mb_detect_encoding($value, ['UTF-8', 'SJIS-win', 'CP932'], true);
                return mb_convert_encoding($value, 'UTF-8', $encoding ?: 'SJIS-win');
            }, $row);

            // 1. 列数チェック
            if (count($row) < 5) {
                $errors[] = [
                    'line' => $lineNumber,
                    'reason' => '列数が不足しています（最低5列必要です）',
                ];
                continue;
            }

            // 2. 日付チェック
            $dateRaw = $row[0] ?? '';
            $dateString = trim($dateRaw, "\" \t\n\r\0\x0B");
            $dateString = str_replace('/', '-', $dateString);

            try {
                $receivedAt = Carbon::parse($dateString);
            } catch (\Exception $e) {
                $errors[] = [
                    'line' => $lineNumber,
                    'reason' => '受付日が不正です:' . $dateRaw,
                ];
                continue;
            }

            // 3. category_id チェック
            $categoryId = trim($row[1] ?? '');
            if ($categoryId === '' || !FaqCategory::where('id', $categoryId)->exists()) {
                $errors[] = [
                    'line' => $lineNumber,
                    'reason' => '診療区分Noが存在しません:' . $categoryId,
                ];
                continue;
            }

            // 4. 質問内容チェック
            $question = trim($row[3] ?? '');
            if (empty($question)) {
                $errors[] = [
                    'line' => $lineNumber,
                    'reason' => '質問内容が空です',
                ];
                continue;
            }

            // 5. 回答内容チェック
            $answer = trim($row[4] ?? '');
            if (empty($answer)) {
                $errors[] = [
                    'line' => $lineNumber,
                    'reason' => '回答内容が空です',
                ];
                continue;
            }

            // バリデーションを通過したデータを保持
            $validRows[] = [
                'received_at' => $receivedAt,
                'question' => $question,
                'answer' => $answer,
                'category_id' => $categoryId,
            ];
        }

        fclose($handle);

        // Phase 2: エラーが1件でもある場合はDB保存を行わず、直接原因のエラーのみ返す
        if (count($errors) > 0) {
            return response()->json([
                'message' => '入力データに不備があるため、インポートを中止しました。',
                'success_count' => 0,
                'error_count' => count($errors),
                'errors' => $errors,
            ]);
        }

        // Phase 3: エラーが1件もない場合のみ全件を一括トランザクションで登録
        $successCount = 0;
        try {
            DB::transaction(function () use ($validRows, &$successCount) {
                foreach ($validRows as $data) {
                    $faq = Faq::create([
                        'question' => $data['question'],
                        'answer' => $data['answer'],
                        'category_id' => $data['category_id'],
                        'created_by' => auth()->id(),
                        'received_at' => $data['received_at'],
                    ]);

                    $faq->received_at = $data['received_at'];
                    $faq->save();

                    $successCount++;
                }
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'データベースへの書き込み時にエラーが発生しました。',
                'success_count' => 0,
                'error_count' => 1,
                'errors' => [
                    ['line' => 0, 'reason' => 'システムエラー: ' . $e->getMessage()]
                ]
            ], 500);
        }

        return response()->json([
            'message' => 'FAQを全件正常にインポートしました',
            'success_count' => $successCount,
            'error_count' => 0,
            'errors' => [],
        ]);
    }
}

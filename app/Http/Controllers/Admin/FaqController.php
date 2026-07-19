<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Traits\FaqSearchTrait;
use App\Models\Faq;
use App\Http\Requests\FaqStoreRequest;
use App\Http\Requests\FaqUpdateRequest;
use App\Models\FaqCategory;

class FaqController extends Controller
{
    use FaqSearchTrait;

    public function index(Request $request) {
        $faqs = $this->searchFaqs($request);

        $faqs->transform(function ($faq) {
            $faq->show_url = route('admin.faqs.show', $faq->id);
            return $faq;
        });

        return response()->json([
            'data' => $faqs,
            'store_url' => route('admin.faqs.store'),
            'export_url' => route('faqs.export') . '?' . http_build_query($request->query()),
            'import_url' => route('admin.faqs.import'),
        ]);
    }

    public function show(Faq $faq) {
        $this->authorize('view', $faq);

        return response()->json([
            'faq' => $faq,
            'index_url' => route('admin.faqs.index'),
            'update_url' => route('admin.faqs.update', $faq->id),
            'delete_url' => route('admin.faqs.destroy', $faq->id),
        ]);
    }

    public function store(FaqStoreRequest $request) {
        $validated = $request->validated();

        $validated['created_by'] = auth()->id();

        $faq = Faq::create($validated);

        if ($request->created_at) {
            $faq->created_at = $request->created_at;
            $faq->save();
        }

        return response()->json([
            'message' => 'FAQを登録しました',
            'faq' => $faq,
        ]);
    }

    public function update(FaqUpdateRequest $request, Faq $faq) {
        $validated = $request->validated();

        $faq->update($validated);

        return response()->json([
            'message' => 'FAQを更新しました',
            'faq' => $faq,
        ]);
    }

    public function destroy(Faq $faq) {
        $faq->delete();

        return response()->json([
            'message' => 'FAQを削除しました',
        ]);
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

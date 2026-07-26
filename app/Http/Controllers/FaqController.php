<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BasePublicContentController;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqController extends BasePublicContentController
{
    protected string $modelClass = Faq::class;
    protected string $routePrefix = 'faqs';

    protected array $indexExtraRelations = ['category'];

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
        $query = $this->search($request);

        $items = $query
            ->when(!empty($this->indexExtraRelations), fn($q) => $q->with($this->indexExtraRelations))
            ->paginate(30);

        $response = $items->toArray();
        $queryParams = $request->except('page');
        $queryString = http_build_query($queryParams);
        $response['export_url'] = '/faqs/export' . ($queryString ? '?' . $queryString : '');

        $response['categories'] = FaqCategory::select('id', 'name')->get();

        return response()->json($response);
    }

    public function export(Request $request) {
        $query = $this->search($request);

        $faqs = $query->when(!empty($this->indexExtraRelations), fn($q) => $q->with($this->indexExtraRelations))->get();

        if (isset($faqs['error'])) {
            return response()->json([
                'message' => $faqs['error'],
            ], 422);
        }

        $csv = fopen('php://temp', 'r+');
        fputs($csv, "\xEF\xBB\xBF");

        fputcsv($csv, [
            '受付日',
            'No.',
            '診療区分（大）',
            '質問内容',
            '回答内容',
        ]);

        foreach ($faqs as $faq) {
            fputcsv($csv, [
                $faq->created_at->format('Y-m-d'),
                $faq->category_id,
                optional($faq->category)->name,
                $faq->question,
                $faq->answer,
            ]);
        }

        rewind($csv);

        return response()->streamDownload(function () use ($csv) {
            fpassthru($csv);
            fclose($csv);
        }, 'faq.csv',[
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

}

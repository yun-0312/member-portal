<?php

namespace App\Http\Controllers;

use App\Traits\FaqSearchTrait;
use Illuminate\Http\Request;
use App\Traits\FiltersByPolicy;

class FaqController extends Controller
{
    use FiltersByPolicy;
    use FaqSearchTrait;

    public function index(Request $request) {
        $faqs = $this->searchFaqs($request);

        if (isset($faqs['error'])) {
            return response()->json([
                'message' => $faqs['error'],
            ], 422);
        }

        $filtered = $this->filterByPolicy($faqs);

        $perPage = 30;
        $page = $request->input('page', 1);

        $paged = $filtered->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $paged,
            'total' => $filtered->count(),
            'current_page' => $page,
            'per_page' => $perPage,
            'export_url' => route('faqs.export') . '?' . http_build_query($request->query()),
        ]);
    }

    public function export(Request $request) {
        $faqs = $this->searchFaqs($request);

        if (isset($faqs['error'])) {
            return response()->json([
                'message' => $faq['error'],
            ], 422);
        }

        $filtered = $this->filterByPolicy($faqs);

        $csv = fopen('php://temp', 'r+');

        fputcsv($csv, [
            '受付日',
            'No.',
            '診療区分（大）',
            '質問内容',
            '回答内容',
        ]);

        foreach ($filtered as $faq) {
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
        }, 'faq.csv');
    }

}

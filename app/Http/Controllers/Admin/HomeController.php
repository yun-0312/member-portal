<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;
use App\Models\ContentCategory;


class HomeController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();
        $user->load('role');

        //お知らせ(letter)
        $letters = Notice::whereHas('category', fn($q) =>$q->where('slug', 'letter'))
            ->whereNotNull('published_at')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'date' => $n->published_at->format('Y-m-d'),
                'url' => "/admin/notices?id={$n->id}",
            ]);

        //回覧(circulate)
        $circulate = Notice::whereHas('category', fn($q) => $q->where('slug', 'circulate'))
            ->whereNotNull('published_at')
            ->selectRaw('DATE(published_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(3)
            ->get()
            ->map(fn($row) => [
                'date' => $row->date,
                'count' => $row->count,
                'url' => "/admin/notices?category=circulate&date={$row->date}",
            ]);

        //schedule
        $schedule = [
            'schedule' => '/admin/schedules',
            'workshop' => '/admin/workshops',
        ];

        //書式（downloads)
        $downloads = ContentCategory::where('section', 'download')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($category) => [
                'key'   => str_replace('-', '_', $category->slug),
                'label' => $category->name,
                'url'   => "/admin/contents?category={$category->slug}",
            ]);

        //その他カテゴリ(categories)
        $dbCategories = ContentCategory::where('section', 'main_menu')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($category) => [
                'key'   => str_replace('-', '_', $category->slug),
                'label' => $category->name,
                'url'   => "/admin/contents?category={$category->slug}",
            ]);

        // DB管理外の特殊メニューを追加
        $extraCategories = collect([
            [
                'key'   => 'workshop_videos',
                'label' => '研修会動画',
                'url'   => '/admin/videos',
            ],
            [
                'key'   => 'faqs',
                'label' => 'コールセンター問い合わせ報告書',
                'url'   => '/admin/faqs',
            ],
        ]);

        $categories = $dbCategories->concat($extraCategories)->values();

        // 6. 理事会専用 (director_only)
        $boardExclusive = ContentCategory::where('slug', 'board-exclusive')->first();
        $directorOnlyUrl = $boardExclusive ? "/admin/contents?category={$boardExclusive->slug}" : null;

        return response()->json([
            'user' => $user,
            'letter' => $letters,
            'letter_url' => '/admin/notices?category=letter',
            'circulate' => $circulate,
            'circulate_url' => '/admin/notices?category=circulate',
            'schedule' => $schedule,
            'downloads' => $downloads,
            'categories' => $categories,
            'director_only' => $directorOnlyUrl,
        ]);

    }
}

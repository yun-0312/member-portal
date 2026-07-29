<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notice;
use App\Models\ContentCategory;

class HomeController extends Controller
{

    public function index(Request $request) {
        $user = $request->user();
        $role = $user->role->name ?? null;

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
                'url' => "/notices?id={$n->id}",
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
                'url' => "/notices?category=circulate&date={$row->date}",
            ]);

        //schedule
        $schedule = [
            'schedule' => '/schedules',
            'workshop' => '/workshops',
        ];

        //書式（downloads)
        $downloads = ContentCategory::visibleTo($user)
            ->where('section', 'download')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($category) => [
                'key'   => str_replace('-', '_', $category->slug),
                'label' => $category->name,
                'url'   => "/contents?category={$category->slug}",
            ]);

        //その他カテゴリ(categories)
        $dbCategories = ContentCategory::visibleTo($user)
            ->where('section', 'main_menu')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($category) => [
                'key'   => str_replace('-', '_', $category->slug),
                'label' => $category->name,
                'url'   => "/contents?category={$category->slug}",
            ]);

        // その他メニューを追加
        $extraCategories = collect([
            [
                'key'   => 'workshop_videos',
                'label' => '研修会動画',
                'url'   => '/videos',
            ],
            [
                'key'   => 'faqs',
                'label' => 'コールセンター問い合わせ報告書',
                'url'   => '/faqs',
            ],
        ]);

        $categories = $dbCategories->concat($extraCategories)->values();

        // 6. 理事会専用 (director_only)
        $boardExclusive = ContentCategory::where('slug', 'board-exclusive')->first();
        $directorOnlyUrl = null;

        if ($boardExclusive && $user->can('view', $boardExclusive)) {
            $directorOnlyUrl = "/contents?category={$boardExclusive->slug}";
        }

        return response()->json([
            'user' => $user,
            'letter' => $letters,
            'letter_url' => '/notices?category=letter',
            'circulate' => $circulate,
            'circulate_url' => '/notices?category=circulate',
            'schedule' => $schedule,
            'downloads' => $downloads,
            'categories' => $categories,
            'director_only' => $directorOnlyUrl,
        ]);

    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notice;
use App\Models\ContentCategory;
use App\Traits\NoticeSearchTrait;

class HomeController extends Controller
{
    use NoticeSearchTrait;

    public function index(Request $request) {
        $user = $request->user();
        $role = $user->role->name;

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
                'url' => route('notices.show', $n->id),
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
                'url' => route('notices.index', [
                    'category' => 'circulate',
                    'date' => $row->date,
                ]),
            ]);

        //schedule
        $schedule = [
            'schedule' => route('schedules.index'),
            'workshop' => route('workshops.index'),
        ];

        //書式（downloads)
        $downloads = [
            'disaster' => route('contents.index', ['category' => 'disaster-manual']),
            'health_check' => route('contents.index', ['category' => 'health-check-manual']),
            'vaccination' => route('contents.index', ['category' => 'vaccination-summary']),
            'public_health' => route('contents.index', ['category' => 'public-health']),
            'registration' => route('contents.index', ['category' => 'registration-change']),
            'commission_fees' => route('contents.index', ['category' => 'commission-fees']),
            'others' => route('contents.index', ['category' => 'others-documents']),
        ];

        //その他カテゴリ(categories)
        $categories = $this->categoriesForRole($role);

        //理事会専用(director_only)
        $boardExclusive = ContentCategory::where('slug', 'board-exclusive')->first();

        $directorOnlyUrl = null;
        if ($boardExclusive && $user->can('view', $boardExclusive)) {
            $directorOnlyUrl = route('contents.index', ['category' => 'board-exclusive']);
        }

        return response()->json([
            'user' => $user,
            'letter' => $letters,
            'circulate' => $circulate,
            'schedule' => $schedule,
            'downloads' => $downloads,
            'categories' => $categories,
            'director_only' => $directorOnlyUrl,
        ]);

    }

        private function categoriesForRole(string $role): array {
        if ($role === 'medical_staff') {
            return [];
        }

        return [
            'board_news' => route('contents.index', ['category' => 'board-news']),
            'committee' => route('contents.index', ['category' => 'committee']),
            'four_medical_association' => route('contents.index', ['category' => 'four-medical-association']),
            'bulletin_magazine' => route('contents.index', ['category' => 'bulletin-magazine']),
            'public_relations' => route('contents.index', ['category' => 'public-relations']),
            'regulations' => route('contents.index', ['category' => 'regulations']),
            'member_directory' => route('contents.index', ['category' => 'member-directory']),
            'general_meeting_agenda' => route('contents.index', ['category' => 'general-meeting-agenda']),
            'others_minutes' => route('contents.index', ['category' => 'others-minutes']),
        ];
    }

}

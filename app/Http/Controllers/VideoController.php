<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Carbon\Carbon;
use App\Traits\FiltersByPolicy;

class VideoController extends Controller
{
    use FiltersByPolicy;

    public function index() {
        $now = Carbon::now();

        $videos = Video::where('published_at', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('expired_at')
                    ->orWhere('expired_at', '>=', $now);
            })
            ->orderBy('published_at', 'desc')->get();

        $filtered = $this->filterByPolicy($videos);

        $filtered->transform(function ($video) {
            $video->show_url = route('videos.show', $video->id);
            return $video;
        });

        return $filtered;
    }

    public function show(Video $video) {
        $this->authorize('view', $video);
        return response()->json([
            'video' => $video,
            'index_url' => route('videos.index'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Http\Requests\VideoStoreRequest;
use App\Http\Requests\VideoUpdateRequest;

class VideoController extends Controller
{
    public function index() {
        $videos = Video::orderBy('published_at', 'desc')->get();

        $videos->transform(function ($video) {
            $video->show_url = route('admin.videos.show', $video->id);
            return $video;
        });

        return response()->json([
            'data' => $videos,
            'store_url' => route('admin.videos.store'),
        ]);
    }

    public function show(Video $video) {
        return response()->json([
            'video' => $video,
            'update_url' => route('admin.videos.update', $video->id),
            'delete_url' => route('admin.videos.destroy', $video->id),
        ]);
    }

    public function store(VideoStoreRequest $request) {
        $validated = $request->validated();

        $video = Video::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => '動画を登録しました',
            'video' => $video,
        ], 201);
    }

    public function update(VideoUpdateRequest $request, Video $video) {
        $validated =$request->validated();

        $video->update($validated);

        return response()->json([
            'message' => '動画を更新しました',
            'video' => $video,
        ]);
    }

    public function destroy(Video $video) {
        $video->delete();

        return response()->json([
            'message' => '動画を削除しました'
        ]);
    }
}

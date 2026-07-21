<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileService;
use Illuminate\Http\Request;
use App\Models\Notice;
use App\Models\Content;
use App\Models\Video;
use App\Models\File;

class FileController extends Controller
{
    protected FileService $fileService;

    public function __construct(FileService $fileService) {
        $this->fileService = $fileService;
    }

    public function listNoticeFiles(Notice $notice) {
        return response()->json([
            'files' => $notice->files,
        ]);
    }

    public function listContentFiles(Content $content) {
        return response()->json([
            'files' => $content->files,
        ]);
    }

    public function listVideoFiles(Video $video) {
        return response()->json([
            'files' => $video->files,
        ]);
    }

    public function uploadToNotice(Request $request, Notice $notice) {
        return $this->upload($request, $notice);
    }

    public function uploadToContent(Request $request, Content $content) {
        return $this->upload($request, $content);
    }

    public function uploadToVideo(Request $request, Video $video) {
        return $this->upload($request, $video);
    }

    private function upload(Request $request, $model) {
        if (!$request->hasFile('file')) {
            return response()->json([
                'message' => 'ファイルがありません'
            ], 400);
        }

        $savedFiles = $this->fileService->uploadMultiple(
            $request->file('file'),
            $model
        );

        return response()->json([
            'message' => 'ファイルをアップロードしました。',
            'file' => $savedFiles,
        ], 201);
    }

    public function destroy(File $file) {
        $this->fileService->delete($file);

        return response()->json([
            'message' => 'ファイルを削除しました',
        ], 200);
    }
}

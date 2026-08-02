<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\FileService;
use Illuminate\Http\Request;
use App\Models\Notice;
use App\Models\Content;
use App\Models\Video;
use App\Models\File;
use Exception;

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

    public function download(File $file) {
        $disk = Storage::disk('public');

        // ファイルが存在するかチェック
        if (!$disk->exists($file->path)) {
            return response()->json([
                'message' => '指定されたファイルが見つかりません。'
            ], 404);
        }

        return $disk->download($file->path, $file->name);
    }

    public function destroy(File $file) {
        $this->fileService->delete($file);

        return response()->json([
            'message' => 'ファイルを削除しました',
        ], 200);
    }

    public function storeMedia(Request $request) {
        // 1. バリデーション
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'ファイルが添付されていません。'], 400);
        }

        $uploadedFile = $request->file('file');

        // 2. ファイルの保存 (storage/app/public/media に保存)
        $path = $uploadedFile->store('media', 'public');

        // 3. DB登録処理をトランザクション化＋例外処理
        try {
            $fileModel = DB::transaction(function () use ($uploadedFile, $path) {
                return File::create([
                    'name' => $uploadedFile->getClientOriginalName(),
                    'path' => $path,
                    'type' => $uploadedFile->getClientMimeType(),
                ]);
            });

            // 4. フロントエンドが欲しい形式で返却 (url も付与)
            return response()->json([
                'message' => '画像をアップロードしました',
                'file'    => [
                    'id'   => $fileModel->id,
                    'name' => $fileModel->name,
                    'url'  => Storage::url($fileModel->path),
                ],
            ], 201);
        } catch (Exception $e) {
            // DB保存に失敗した場合、保存してしまった物理ファイルを削除する
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            // ログ出力をしてエラーレスポンスを返す
            Log::error('Media Upload Error: ' . $e->getMessage());

            return response()->json([
                'message' => '画像の保存に失敗しました。',
            ], 500);
        }
    }

    // 汎用メディアアップロード
    public function uploadMedia(Request $request) {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
        ]);

        // FileService等で保存し、filesテーブルにレコード作成
        $file = $this->fileService->storeMedia($request->file('file'));

        return response()->json([
            'message' => 'アップロードしました',
            'file' => [
                'id'   => $file->id,
                'name' => $file->name,
                'url'  => Storage::disk('public')->url($file->path),
            ]
        ], 201);
    }

    // メディア一覧取得
    public function listMedia(Request $request) {
        $files = File::where('type', 'like', 'image/%')
            ->latest()
            ->paginate(24);

        return response()->json($files);
    }
}

<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Content;
use App\Models\Notice;
use App\Models\Video;

class FileService
{
    public function uploadMultiple(array $files, $model): array
    {
        $dir = $this->resolveDirectory($model);

        $saved = [];
        $path = [];

        try {
            foreach ($files as $file) {
                $storedPath = $file->store($dir, 'public');
                $paths[] = $storedPath;

                $saved[] = $model->files()->create([
                    'path' => $storedPath,
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getClientMimeType(),
                ]);
            }

            return $saved;

        } catch (\Throwable $e) {
            foreach ($files as $file) {
                $saved[] = $this->store($file, $model, $dir);
            }

            throw $e;
        }

    }

    public function store(UploadedFile $file, $model, string $dir): File
    {
        $path = $file->store($dir, 'public');

        return $model->files()->create([
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'type' => $file->getClientMimeType(),
        ]);
    }

    public function delete(File $file): void
    {
        try {
            if (Storage::disk('public')->exists($file->path)) {
                Storage::disk('public')->delete($file->path);
            }
            $file->delete();
        } catch (\Throwable $e) {
            Log::error($e);
            throw $e;
        }
    }

    public function deleteByIds($model, array $ids): void
    {
        $model->files()
            ->whereIn('id', $ids)
            ->get()
            ->each(fn ($file) => $this->delete($file));
    }

    private function resolveDirectory($model): string
    {
        return match (true) {
            $model instanceof Notice => 'notice_files',
            $model instanceof Content => 'content_files',
            $model instanceof Video => 'video_files',
            default => 'uploads',
        };
    }
}


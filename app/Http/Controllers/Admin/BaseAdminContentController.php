<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\FileService;
use Illuminate\Database\Eloquent\Model;

class BaseAdminContentController extends Controller
{
    protected string $modelClass;
    protected string $routePrefix;
    protected string $storeRequestClass = Request::class;
    protected string $updateRequestClass = Request::class;
    protected array $extraRelations = [];
    protected FileService $fileService;

    public function __construct(FileService $fileService) {
        $this->fileService = $fileService;
    }

    protected function newModel() {
        return new $this->modelClass;
    }

    protected function validateRequest(Request $request, string $requestClass): array {
        if ($requestClass === Request::class) {
            return $request->all();
        }
        // 指定された FormRequest インスタンスを生成してバリデーションを実行する
        $formRequest = app($requestClass);

        return $formRequest->validated();
    }

    protected function findModel($item): Model {
        if ($item instanceof Model) {
            return $item;
        }

        return $this->newModel()->findOrFail($item);
    }

    public function index(Request $request) {
        $query = $this->search($request);

        $items = $query
            ->visibleTo($request->user())
            ->latest('published_at')
            ->paginate(10)
            ->through(function ($item) {
                $item->show_url = route("admin.{$this->routePrefix}.show", $item->id);
                return $item;
            });

        $items = $items->toArray();
        $items['store_url'] = route("admin.{$this->routePrefix}.store");

        return response()->json($items);
    }

    public function show($id) {
        $item = $this->findModel($id);

        $item->load(array_merge(['files', 'roles', 'creator'], $this->extraRelations));

        return response()->json([
            'item' => $item,
            'index_url' => route("admin.{$this->routePrefix}.index"),
            'update_url' => route("admin.{$this->routePrefix}.update", $item->id),
            'delete_url' => route("admin.{$this->routePrefix}.destroy", $item->id),
        ]);
    }

    public function store(Request $request) {
        $validated = $this->validateRequest($request, $this->storeRequestClass);
        $item = DB::transaction(function () use ($request, $validated) {


            $item = $this->newModel()->create([
                ...$validated,
                'created_by' => $request->user()->id,
            ]);

            if ($request->hasFile('file')) {
                $this->fileService->uploadMultiple(
                    $request->file('file'),
                    $item
                );
            }

            if ($request->filled('roles')) {
                $item->roles()->sync($request->roles ?? []);
            }

            return $item;
        });

        return response()->json([
            'message' => '登録しました',
            'item' => $item->load('files', 'roles'),
        ], 201);
    }

    public function update(Request $request, $id) {
        $item = $this->findModel($id);
        $validated = $this->validateRequest($request, $this->updateRequestClass);
        DB::transaction(function () use ($request, $item, $validated) {
            $item->update($validated);

            if ($request->hasFile('file')) {
                $this->fileService->uploadMultiple(
                    $request->file('file'),
                    $item
                );
            }

            if ($request->filled('delete_file_ids')) {
                $this->fileService->deleteByIds(
                    $item,
                    $request->delete_file_ids
                );
            }

            if ($request->has('roles')) {
                $item->roles()->sync($request->roles ?? []);
            }
        });

        return response()->json([
            'message' => '更新しました',
            'item' => $item->refresh()->load('files', 'roles'),
        ]);
    }

    public function destroy($id) {
        $item = $this->findModel($id);

        DB::transaction(function () use ($item) {
            $item->load('files');

            $item->files->each(fn($file) => $this->fileService->delete($file));

            $item->roles()->detach();

            $item->delete();
        });

        return response()->json([
            'message' => '削除しました'
        ]);
    }
}

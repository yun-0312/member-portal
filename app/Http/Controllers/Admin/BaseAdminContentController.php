<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\FileService;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ContentSearchTrait;

class BaseAdminContentController extends Controller
{
    use ContentSearchTrait;

    //モデル読み込み用
    protected string $modelClass;

    protected function newModel() {
        return new $this->modelClass;
    }

    protected function findModel($item): Model {
        if ($item instanceof Model) {
            return $item;
        }

        return $this->newModel()->findOrFail($item);
    }

    protected string $routePrefix;
    protected string $publishedDateColumn = 'published_at';

    //Request読み込み用
    protected string $storeRequestClass = Request::class;
    protected string $updateRequestClass = Request::class;

    protected function validateRequest(Request $request, string $requestClass): array {
        if ($requestClass === Request::class) {
            return $request->all();
        }
        // 指定された FormRequest インスタンスを生成してバリデーションを実行する
        $formRequest = app($requestClass);

        return $formRequest->validated();
    }

    //ファイルがあるコンテンツ用
    protected FileService $fileService;

    public function __construct(FileService $fileService) {
        $this->fileService = $fileService;
    }

    //index、showのリレーション設定
    protected array $indexExtraRelations = [];
    protected array $showExtraRelations = [];

    protected function search(Request $request) {
        return $this->applyContentSearch($request);
    }


    public function index(Request $request) {
        $this->authorize('viewAny', $this->modelClass);
        $query = $this->search($request);

        $items = $query
            ->when(!empty($this->indexExtraRelations), fn($q) => $q->with($this->indexExtraRelations))
            ->latest($this->publishedDateColumn)
            ->paginate(15)
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
        $this->authorize('view', $item);

        if (!empty($this->showExtraRelations)) {
            $item->load($this->showExtraRelations);
        }

        return response()->json([
            'item' => $item,
            'index_url' => route("admin.{$this->routePrefix}.index"),
            'update_url' => route("admin.{$this->routePrefix}.update", $item->id),
            'delete_url' => route("admin.{$this->routePrefix}.destroy", $item->id),
        ]);
    }

    public function store(Request $request) {
        $this->authorize('create', $this->modelClass);

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

            // roles リレーションが存在して、リクエストに含まれている場合のみ sync
            if (method_exists($item, 'roles') && $request->has('roles')) {
                $item->roles()->sync($request->roles ?? []);
            }

            return $item;
        });

        // 保存後にレスポンスで返したいリレーションも showExtraRelations を活用
        if (!empty($this->showExtraRelations)) {
            $item->load($this->showExtraRelations);
        }

        return response()->json([
            'message' => '登録しました',
            'item' => $item,
        ], 201);
    }

    public function update(Request $request, $id) {
        $item = $this->findModel($id);

        $this->authorize('update', $item);

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

            if (method_exists($item, 'roles') && $request->has('roles')) {
                $item->roles()->sync($request->roles ?? []);
            }
        });

        $item->refresh();
        if (!empty($this->showExtraRelations)) {
            $item->load($this->showExtraRelations);
        }

        return response()->json([
            'message' => '更新しました',
            'item' => $item,
        ]);
    }

    public function destroy($id) {
        $item = $this->findModel($id);
        $this->authorize('delete', $item);

        DB::transaction(function () use ($item) {
            if (method_exists($item, 'files')) {
                $item->load('files');
                $item->files->each(fn($file) => $this->fileService->delete($file));
            }

            if (method_exists($item, 'roles')) {
                $item->roles()->detach();
            }

            $item->delete();
        });

        return response()->json([
            'message' => '削除しました'
        ]);
    }
}

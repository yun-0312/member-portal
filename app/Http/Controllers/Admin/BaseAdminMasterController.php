<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class BaseAdminMasterController extends Controller
{
    protected string $modelClass;
    protected string $routePrefix;
    protected string $storeRequestClass = Request::class;
    protected string $updateRequestClass = Request::class;

    protected string $sortColumn = 'id';
    protected string $sortDirection = 'asc';

    protected array $extraRelations = [];

    protected function newModel() {
        return new $this->modelClass;
    }

    protected function findModel($item): Model {
        if ($item instanceof Model) {
            return $item;
        }

        return $this->newModel()->findOrFail($item);
    }

    protected function validateRequest(Request $request, string $requestClass): array {
        if ($requestClass === Request::class) {
            return $request->all();
        }
        // 指定された FormRequest インスタンスを生成してバリデーションを実行する
        $formRequest = app($requestClass);

        return $formRequest->validated();
    }


    public function index(Request $request) {
        $query = $this->newModel()->query();

        if (!empty($this->extraRelations)) {
            $query->with($this->extraRelations);
        }

        $items = $query
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate($request->input('per_page',20))
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

        //$extraRelationsが指定可
        if (!empty($this->extraRelations)) {
            $item->load($this->extraRelations);
        }

        return response()->json([
            'item' => $item,
            'index_url' => route("admin.{$this->routePrefix}.index"),
            'update_url' => route("admin.{$this->routePrefix}.update", $item->id),
            'delete_url' => route("admin.{$this->routePrefix}.destroy", $item->id),
        ]);
    }

    public function store(Request $request) {
        $validated = $this->validateRequest($request, $this->storeRequestClass);

        $item = $this->newModel()->create($validated);

        return response()->json([
            'message' => '登録しました',
            'item' => $item,
        ], 201);
    }

    public function update(Request $request, $id) {
        $item = $this->findModel($id);
        $validated = $this->validateRequest($request, $this->updateRequestClass);

        $item->update($validated);

        return response()->json([
            'message' => '更新しました',
            'item' => $item->refresh(),
        ]);
    }

    public function destroy($id) {
        $item = $this->findModel($id);

        $item->delete();

        return response()->json([
            'message' => '削除しました'
        ]);
    }
}

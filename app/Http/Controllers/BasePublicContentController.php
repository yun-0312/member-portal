<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FileService;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ContentSearchTrait;


class BasePublicContentController extends Controller
{
    use ContentSearchTrait;

    protected string $modelClass;
    protected string $routePrefix;
    protected FileService $fileService;
    protected array $extraRelations = [];
    protected string $publishedDateColumn = 'published_at';

    public function __construct(FileService $fileService) {
        $this->fileService = $fileService;
    }

    protected function newModel() :Model {
        return new $this->modelClass;
    }

    protected function findModel($item): Model {
        if ($item instanceof Model) {
            return $item;
        }

        return $this->newModel()->findOrFail($item);
    }

    protected function search(Request $request) {
        return $this->applyContentSearch($request);
    }

    protected array $indexExtraRelations = [];

    public function index(Request $request) {
        $query = $this->search($request);

        $items = $query
            ->when(!empty($this->indexExtraRelations), fn($q) => $q->with($this->indexExtraRelations))
            ->visibleTo($request->user())
            ->latest($this->publishedDateColumn)
            ->paginate(15);

        return response()->json($items);
    }

    public function show($id) {
        $item = $this->findModel($id);

        $this->authorize('view', $item);

        $item->load(array_merge(['files', 'roles'], $this->extraRelations));

        return response()->json([
            'item' => $item,
            'index_url' => "/admin/$this->routePrefix",
        ]);
    }
}

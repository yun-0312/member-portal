<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FileService;
use Illuminate\Database\Eloquent\Model;


class BasePublicContentController extends Controller
{
    protected string $modelClass;
    protected string $routePrefix;
    protected FileService $fileService;
    protected array $extraRelations = [];

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

    public function index(Request $request) {
        $query = $this->search($request);

        $items = $query
            ->published()
            ->visibleTo($request->user())
            ->latest('published_at')
            ->paginate(10)
            ->through(function ($item) {
                $item->show_url = route("{$this->routePrefix}.show", $item->id);
                return $item;
            });

        return response()->json($items);
    }

    public function show($id) {
        $item = $this->findModel($id);

        $item->load(array_merge(['files', 'roles'], $this->extraRelations));

        return response()->json([
            'item' => $item,
            'index_url' => route("{$this->routePrefix}.index"),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BasePublicContentController;
use App\Models\Video;
use Illuminate\Http\Request;
use App\Traits\VideoSearchTrait;

class VideoController extends BasePublicContentController
{
    use VideoSearchTrait;

    protected string $modelClass = Video::class;
    protected string $routePrefix = 'videos';

    public function search(Request $request) {
        return $this->searchVideos($request);
    }

    //show_url付与のためオーバーライド
    public function index(Request $request) {
        $query = $this->search($request);

        $items = $query
            ->when(!empty($this->indexExtraRelations), fn($q) => $q->with($this->indexExtraRelations))
            ->visibleTo($request->user())
            ->latest($this->publishedDateColumn)
            ->paginate(15);

        $items->through(function ($item) {
            $item->show_url = "/$this->routePrefix/$item->id";

            return $item;
        });

        return response()->json($items);
    }

}

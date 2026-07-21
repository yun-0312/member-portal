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
}

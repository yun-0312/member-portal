<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminContentController;
use App\Traits\VideoSearchTrait;
use App\Models\Video;
use Illuminate\Http\Request;
use App\Http\Requests\VideoStoreRequest;
use App\Http\Requests\VideoUpdateRequest;

class VideoController extends BaseAdminContentController
{
    use VideoSearchTrait;

    protected string $modelClass = Video::class;
    protected string $routePrefix = 'videos';

    protected string $storeRequestClass = VideoStoreRequest::class;
    protected string $updateRequestClass = VideoUpdateRequest::class;

    public function search(Request $request) {
        return $this->searchVideos($request);
    }

}

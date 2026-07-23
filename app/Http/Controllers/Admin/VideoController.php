<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminContentController;
use App\Models\Video;
use App\Http\Requests\VideoStoreRequest;
use App\Http\Requests\VideoUpdateRequest;

class VideoController extends BaseAdminContentController
{
    protected array $indexExtraRelations = ['files'];
    protected array $showExtraRelations = ['files'];
    protected string $modelClass = Video::class;
    protected string $routePrefix = 'videos';

    protected string $storeRequestClass = VideoStoreRequest::class;
    protected string $updateRequestClass = VideoUpdateRequest::class;

}

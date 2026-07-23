<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminContentController;
use App\Models\Workshop;
use App\Http\Requests\WorkshopStoreRequest;
use App\Http\Requests\WorkshopUpdateRequest;

class WorkshopController extends BaseAdminContentController
{
    protected string $modelClass = Workshop::class;
    protected string $routePrefix = 'workshops';
    protected string $publishedDateColumn = 'start_at';

    protected string $storeRequestClass = WorkshopStoreRequest::class;
    protected string $updateRequestClass = WorkshopUpdateRequest::class;

}

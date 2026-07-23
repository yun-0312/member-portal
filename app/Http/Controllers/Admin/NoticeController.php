<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminContentController;
use App\Models\Notice;
use App\Http\Requests\NoticeStoreRequest;
use App\Http\Requests\NoticeUpdateRequest;

class NoticeController extends BaseAdminContentController
{
    protected array $indexExtraRelations = ['category', 'files', 'roles'];
    protected array $showExtraRelations = ['category', 'files', 'roles'];

    protected string $modelClass = Notice::class;
    protected string $routePrefix = 'notices';

    protected string $storeRequestClass = NoticeStoreRequest::class;
    protected string $updateRequestClass = NoticeUpdateRequest::class;

}

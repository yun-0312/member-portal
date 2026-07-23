<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminContentController;
use App\Models\Content;
use App\Http\Requests\ContentStoreRequest;
use App\Http\Requests\ContentUpdateRequest;

class ContentController extends BaseAdminContentController
{
    protected array $indexExtraRelations = ['category', 'subcategory', 'roles'];
    protected array $showExtraRelations = ['category', 'subcategory','files', 'creator', 'roles'];
    protected string $modelClass = Content::class;
    protected string $routePrefix = 'contents';

    protected string $storeRequestClass = ContentStoreRequest::class;
    protected string $updateRequestClass = ContentUpdateRequest::class;

}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminContentController;
use App\Traits\ContentSearchTrait;
use Illuminate\Http\Request;
use App\Models\Content;
use App\Http\Requests\ContentStoreRequest;
use App\Http\Requests\ContentUpdateRequest;

class ContentController extends BaseAdminContentController
{
    use ContentSearchTrait;

    protected array $extraRelations = ['category', 'subcategory'];
    protected string $modelClass = Content::class;
    protected string $routePrefix = 'contents';

    protected string $storeRequestClass = ContentStoreRequest::class;
    protected string $updateRequestClass = ContentUpdateRequest::class;

    public function search(Request $request) {
        return $this->searchContents($request);
    }

}

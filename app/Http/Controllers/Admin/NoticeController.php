<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminContentController;
use Illuminate\Http\Request;
use App\Traits\NoticeSearchTrait;
use App\Models\Notice;
use App\Http\Requests\NoticeStoreRequest;
use App\Http\Requests\NoticeUpdateRequest;

class NoticeController extends BaseAdminContentController
{
    use NoticeSearchTrait;

    protected array $extraRelations = ['category'];

    protected string $modelClass = Notice::class;
    protected string $routePrefix = 'notices';

    protected string $storeRequestClass = NoticeStoreRequest::class;
    protected string $updateRequestClass = NoticeUpdateRequest::class;

    public function search(Request $request) {
        return $this->searchNotices($request);
    }

}

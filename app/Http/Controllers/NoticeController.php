<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BasePublicContentController;
use App\Traits\NoticeSearchTrait;
use Illuminate\Http\Request;
use App\Models\Notice;

class NoticeController extends BasePublicContentController
{
    use NoticeSearchTrait;

    protected array $extraRelations = ['category'];

    protected string $modelClass = Notice::class;
    protected string $routePrefix = 'notices';

    public function search(Request $request) {
        return $this->searchNotices($request, ['category', 'files']);
    }

}

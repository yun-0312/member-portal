<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BasePublicContentController;

use App\Models\Notice;

class NoticeController extends BasePublicContentController
{

    protected array $indexExtraRelations = ['category', 'files', 'roles'];

    protected string $modelClass = Notice::class;
    protected string $routePrefix = 'notices';

}

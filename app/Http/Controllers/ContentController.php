<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BasePublicContentController;
use App\Models\Content;

class ContentController extends BasePublicContentController
{
    protected array $extraRelations = ['category', 'subcategory'];

    protected string $modelClass = Content::class;
    protected string $routePrefix = 'contents';

    public function years() {
        return Content::selectRaw('YEAR(published_at) as year')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();
    }

}

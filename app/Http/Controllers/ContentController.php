<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BasePublicContentController;
use App\Traits\ContentSearchTrait;
use Illuminate\Http\Request;
use App\Models\Content;

class ContentController extends BasePublicContentController
{
    use ContentSearchTrait;

    protected array $extraRelations = ['category', 'subcategory'];

    protected string $modelClass = Content::class;
    protected string $routePrefix = 'contents';

    public function search(Request $request) {
        return $this->searchContents($request);
    }

    public function years() {
        return Content::selectRaw('YEAR(published_at) as year')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();
    }

}

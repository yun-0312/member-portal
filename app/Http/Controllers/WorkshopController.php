<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Traits\FiltersByPolicy;

class WorkshopController extends Controller
{
    use FiltersByPolicy;

    public function index() {
        $workshops = Workshop::orderBy('start_at', 'desc')->get();
        $filtered = $this->filterByPolicy($workshops);

        $perPage = 20;
        $page = request()->input('page', 1);
        $offset = ($page - 1) * $perPage;

        $paginated = $filtered->slice($offset, $perPage)->values();

        $paginated->transform(function ($workshop) {
            $workshop->show_url = route('workshops.show', $workshop->id);
            return $workshop;
        });

        return response()->json([
            'data' => $paginated,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $filtered->count(),
            'last_page' => ceil($filtered->count() / $perPage),
        ]);
    }

    public function show(Workshop $workshop) {
        $this->authorize('view', $workshop);
        return response()->json([
            'workshop' => $workshop,
            'index_url' => route('workshops.index'),
        ]);
    }

}

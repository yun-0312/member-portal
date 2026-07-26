<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Workshop;

class WorkshopController extends Controller
{

    public function index(Request $request) {
        $this->authorize('viewAny', Workshop::class);

        $workshops = Workshop::orderBy('start_at', 'desc')
            ->paginate(20);

        return response()->json($workshops);
    }

}

<?php

namespace App\Http\Controllers\Layout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FooterController extends Controller
{
    public function index()
    {
        return [
            'copyright' => '© 2026 Medical Portal',
        ];
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notice;


class NoticeController extends Controller
{
    public function index() {
        return Notice::orderBy('published_at', 'desc')->get();
    }

    public function show(Notice $notices) {
        return $notices;
    }
}

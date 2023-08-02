<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class TopController extends Controller
{
    /**
     * トップページの表示
     */
    public function index(): View
    {
        return view('top');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TermsController extends Controller
{
    /**
     * 利用規約の表示
     * 
     * @return Illuminate\View\View
     */
    public function index(): View
    {
        return view('terms');
    }
}

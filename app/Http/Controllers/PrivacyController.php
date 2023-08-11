<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PrivacyController extends Controller
{
    /**
     * プライバシーポリシーの表示
     * 
     * @return Illuminate\View\View
     */
    public function index(): View
    {
        return view('privacy');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PrivacyController extends Controller
{
    /**
     * プライバシーポリシーの表示
     */
    public function index(): View
    {
        return view('privacy');
    }
}

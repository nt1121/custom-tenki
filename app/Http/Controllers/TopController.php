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

    /**
     * 利用規約の表示
     */
    public function showTermsOfServicePage(): View
    {
        return view('terms');
    }

    /**
     * プライバシーポリシーの表示
     */
    public function showPrivacyPolicyPage(): View
    {
        return view('privacy');
    }
}

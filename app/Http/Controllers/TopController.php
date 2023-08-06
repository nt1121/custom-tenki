<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class TopController extends Controller
{
    /**
     * トップページの表示
     */
    public function index(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/weather');
        }

        return view('top');
    }
}

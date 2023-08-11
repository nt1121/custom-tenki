<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TopController extends Controller
{
    /**
     * トップページの表示
     * 
     * @return Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function index(): View | RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/weather');
        }

        return view('top');
    }
}

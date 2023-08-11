<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class WeatherController extends Controller
{
    /**
     * SPA用のテンプレートを表示する
     * 
     * @return Illuminate\View\View
     */
    public function index(): View
    {
        return view('weather.index', ['isWeather' => true]);
    }
}

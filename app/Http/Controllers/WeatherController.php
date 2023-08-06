<?php

namespace App\Http\Controllers;

class WeatherController extends Controller
{
    public function index()
    {
        return view('weather.index', ['isWeather' => true]);
    }
}

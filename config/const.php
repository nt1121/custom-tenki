<?php

return [
    'weather_api' => [
        'api_key' => env('WEATHER_API_KEY'),
        'max_requests_per_minute' => 20, // １分間にAPIにリクエストできる最大回数
    ]
];

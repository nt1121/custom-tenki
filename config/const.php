<?php

return [
    'weather_api' => [
        'api_key' => env('WEATHER_API_KEY'),
        'three_hour_forecast' => [
            'endpoint' => 'https://api.openweathermap.org/data/2.5/forecast',
            'max_requests_per_minute' => 20, // １分間にAPIにリクエストできる最大回数
            'cache_key' => 'three_hour_forecast_data_area_id_',
            'rate_limit_key' => 'three-hour-forecast-api-request',
        ],
    ],
    'system_admin_email_address' => env('SYSTEM_ADMIN_EMAIL_ADDRESS'),
    'test_user_login_enabled' => env('TEST_USER_LOGIN_ENABLED', false),
    'area_group_and_children_cache_key' => 'area_group_and_children_area_group_id_',
    'test_user_email1' => env('TEST_USER_EMAIL1'),
    'test_user_email2' => env('TEST_USER_EMAIL2'),
];

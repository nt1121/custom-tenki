<?php

return [
    'weather_api' => [
        'api_key' => env('WEATHER_API_KEY'),
        'max_requests_per_minute' => 20, // １分間にAPIにリクエストできる最大回数
    ],
    'system_admin_email_address' => env('SYSTEM_ADMIN_EMAIL_ADDRESS'),
    'test_user_login_enabled' => env('TEST_USER_LOGIN_ENABLED', false),
];

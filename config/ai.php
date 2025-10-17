<?php

return [
    // Set AI_DRIVER=http to use the Python service in ml_service
    'driver' => env('AI_DRIVER', 'http'),
    'base_url' => env('AI_BASE_URL', 'http://127.0.0.1:5001'),
    'api_key' => env('AI_API_KEY', ''),
    'timeout' => (float) env('AI_TIMEOUT', 30),
];

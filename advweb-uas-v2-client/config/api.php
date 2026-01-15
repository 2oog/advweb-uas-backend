<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the backend API. This should point to your Laravel
    | backend's API endpoint.
    |
    */
    'url' => env('API_URL', 'http://localhost/advweb-uas-v2/api'),
    'server_url' => env('SERVER_URL', 'http://localhost/advweb-uas-v2'),

    /*
    |--------------------------------------------------------------------------
    | API Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests.
    |
    */
    'timeout' => env('API_TIMEOUT', 30),
];

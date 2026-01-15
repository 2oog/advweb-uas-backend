<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (API-Only Backend)
|--------------------------------------------------------------------------
|
| This backend serves as an API-only backend for the decoupled frontend.
| All web routes have been removed. Please use the API routes in api.php.
| The frontend client is located at: advweb-uas-v2-client
|
*/

// Fallback route - redirect to API documentation or return JSON response
Route::fallback(function () {
    return response()->json([
        'message' => 'This is an API-only backend. Please use the API endpoints.',
        'api_prefix' => '/api',
        'frontend_client' => 'advweb-uas-v2-client'
    ], 404);
});

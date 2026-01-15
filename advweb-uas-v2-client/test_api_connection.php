<?php

use App\Services\AuthService;
use App\Services\MenuService;
use Illuminate\Support\Facades\Session;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Mock Session (Array driver is essentially mock in CLI, but we need to ensure it persists for the instance)
// Actually, in CLI, Session facade driver 'array' should work if configured, or 'file'.
// Let's just manually test the flow.

echo "Testing API Connection...\n";
echo "API_URL: " . config('api.url') . "\n";

$authService = new AuthService();
$menuService = new MenuService();

// 1. Try Login
echo "\nAttempting Login (using hardcoded credentials from Postman)... \n";
// CAUTION: User postman had x@x.com / qwerasdf.
$email = 'x@x.com';
$password = 'qwerasdf';

$loginResult = $authService->login($email, $password);

if ($loginResult['success']) {
    echo "Login Successful!\n";
    echo "Token: " . substr($loginResult['data']['access_token'], 0, 10) . "...\n";
    
    // Set token manually in session for the next service call if it relies on facade (which it does)
    // But wait, AuthService::login() already calls Session::put().
    // Does CLI persist session? 
    // In Array driver: yes for the lifetime of script.
    
    // 2. Fetch Menu
    echo "\nAttempting to fetch menu items...\n";
    $menuResult = $menuService->getAll();
    
    if ($menuResult['success']) {
        echo "Menu Items Fetched Successfully!\n";
        echo "Count: " . count($menuResult['data']) . "\n";
        print_r(array_slice($menuResult['data'], 0, 1)); // Show first item
    } else {
        echo "Failed to fetch menu items.\n";
        echo "Status: " . $menuResult['status'] . "\n";
        echo "Error: " . $menuResult['error'] . "\n";
        if (isset($menuResult['errors'])) print_r($menuResult['errors']);
    }

} else {
    echo "Login Failed.\n";
    echo "Status: " . $loginResult['status'] . "\n";
    echo "Error: " . $loginResult['error'] . "\n";
    if (isset($loginResult['errors'])) print_r($loginResult['errors']);
}

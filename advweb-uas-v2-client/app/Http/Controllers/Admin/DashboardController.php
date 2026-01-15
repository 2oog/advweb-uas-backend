<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use App\Services\SalesService;
use App\Services\UserService;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    protected SalesService $salesService;
    protected MenuService $menuService;
    protected UserService $userService;

    public function __construct(SalesService $salesService, MenuService $menuService, UserService $userService)
    {
        $this->salesService = $salesService;
        $this->menuService = $menuService;
        $this->userService = $userService;
    }

    /**
     * Display admin dashboard
     */
    public function index()
    {
        // Fetch sales stats
        $statsResponse = $this->salesService->getStats();
        $stats = $statsResponse['success'] ? $statsResponse['data'] : [
            'total_orders' => 0,
            'total_revenue' => 0,
        ];

        // Fetch menu items
        $menuResponse = $this->menuService->getAll();
        $menuItems = $menuResponse['success'] ? $menuResponse['data'] : [];

        // Fetch users
        $usersResponse = $this->userService->getAll();
        $users = $usersResponse['success'] ? $usersResponse['data'] : [];

        // Pass API token for JS calls (menu management uses fetch for better UX)
        $token = Session::get('api_token');
        $apiUrl = config('api.url');

        return view('admin.dashboard', compact('stats', 'menuItems', 'users', 'token', 'apiUrl'));
    }
}

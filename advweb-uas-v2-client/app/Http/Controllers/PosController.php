<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\MenuService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PosController extends Controller
{
    protected MenuService $menuService;
    protected OrderService $orderService;
    protected AuthService $authService;

    public function __construct(MenuService $menuService, OrderService $orderService, AuthService $authService)
    {
        $this->menuService = $menuService;
        $this->orderService = $orderService;
        $this->authService = $authService;
    }

    /**
     * Display the POS index page with menu items
     */
    public function index()
    {
        $menuResponse = $this->menuService->getAll();
        $menuItems = $menuResponse['success'] ? $menuResponse['data'] : [];

        // Get API token for JavaScript fetch calls (allowed exception for POS page)
        $token = Session::get('api_token');
        $apiUrl = config('api.url');

        \Illuminate\Support\Facades\Log::info('PosController@index', [
            'token_exists' => !empty($token),
            'apiUrl' => $apiUrl,
            'menuResponse_success' => $menuResponse['success'] ?? false,
            'menuItems_count' => count($menuItems),
            'menuResponse_error' => $menuResponse['error'] ?? null,
        ]);

        return view('pos.index', compact('menuItems', 'token', 'apiUrl'));
    }

    /**
     * Display order history with filters
     */
    public function history(Request $request)
    {
        $filters = [
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'sort_by' => $request->input('sort_by', 'order_date'),
            'sort_dir' => $request->input('sort_dir', 'desc'),
        ];

        // Remove null values
        $filters = array_filter($filters, fn($v) => $v !== null);

        $ordersResponse = $this->orderService->getAll($filters);
        $orders = $ordersResponse['success'] ? $ordersResponse['data'] : [];

        // Get API token for JavaScript fetch calls (for reprint functionality)
        $token = Session::get('api_token');
        $apiUrl = config('api.url');

        return view('pos.history', compact('orders', 'filters', 'token', 'apiUrl'));
    }

    /**
     * Create an order (called from checkout form)
     */
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'payment_method' => 'required|string',
            'table_number' => 'required|string',
            'subtotal' => 'required|numeric',
            'tax_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'tax_percent' => 'required|numeric',
            'global_discount_percent' => 'required|numeric',
            'order_items' => 'required|array',
        ]);

        $response = $this->orderService->create($data);

        if ($response['success']) {
            return response()->json([
                'success' => true,
                'order' => $response['data'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $response['error'] ?? 'Order failed',
        ], 400);
    }

    /**
     * Print an order
     */
    public function print(int $id)
    {
        $response = $this->orderService->print($id);

        if ($response['success']) {
            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => $response['error'] ?? 'Print failed',
        ], 400);
    }
}

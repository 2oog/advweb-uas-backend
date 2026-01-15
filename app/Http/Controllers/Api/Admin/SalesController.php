<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    /**
     * Get sales statistics for admin dashboard
     */
    public function index()
    {
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_amount');
        
        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayRevenue = Order::whereDate('created_at', today())->sum('total_amount');

        return response()->json([
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'today_orders' => $todayOrders,
            'today_revenue' => $todayRevenue,
        ]);
    }
}

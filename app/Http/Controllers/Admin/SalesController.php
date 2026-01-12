<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class SalesController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $revenue = Order::sum('total_amount');

        return response()->json([
            'total_orders' => $totalOrders,
            'total_revenue' => $revenue,
        ]);
    }
}

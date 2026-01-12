<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with('orderItems');

        if ($request->has('month') && $request->has('year')) {
            $query->whereYear('order_date', $request->year)
                ->whereMonth('order_date', $request->month);
        }

        if ($request->has('sort_by')) {
            $sortDir = $request->get('sort_dir', 'desc');
            $query->orderBy($request->sort_by, $sortDir);
        } else {
            // Default sort
            $query->orderBy('order_date', 'desc');
        }

        return $query->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
            'table_number' => 'required|string',
            'subtotal' => 'required|integer',
            'tax_amount' => 'required|integer',
            'total_amount' => 'required|integer',
            'tax_percent' => 'required|numeric|min:0|max:100',
            'global_discount_percent' => 'required|numeric|min:0|max:100',
            'order_items' => 'required|array',
            'order_items.*.id' => 'required|exists:menu_items,id',
            'order_items.*.menu_name' => 'required|string',
            'order_items.*.quantity' => 'required|integer|min:1',
            'order_items.*.price' => 'required|integer',
            'order_items.*.subtotal' => 'required|integer',
            'order_items.*.item_discount' => 'numeric',
        ]);

        return DB::transaction(function () use ($validated) {
            $order = Order::create([
                'order_date' => now(),
                'table_number' => $validated['table_number'],
                'subtotal' => $validated['subtotal'],
                'tax_amount' => $validated['tax_amount'],
                'total_amount' => $validated['total_amount'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'PAID',
                'tax_percent' => $validated['tax_percent'] ?? 0.1,
                'global_discount_percent' => $validated['global_discount_percent'] ?? 0,
            ]);

            $orderItemsData = [];
            foreach ($validated['order_items'] as $item) {
                // We primarily trust the client, but ID is validated to exist.
                // We use the client provided name, price, subtotal.

                $orderItemsData[] = [
                    'menu_item_id' => $item['id'],
                    'menu_name' => $item['menu_name'],
                    'quantity' => $item['quantity'],
                    'price_at_time' => $item['price'],
                    'subtotal' => $item['subtotal'],
                    'item_discount' => $item['item_discount'] ?? 0,
                ];
            }

            $order->orderItems()->createMany($orderItemsData);

            return $order->load('orderItems');
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Order::with('orderItems')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Just for updating payment status
        $order = Order::findOrFail($id);
        $order->update($request->only(['payment_status']));

        return $order;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // No deleting orders :3, but here for completeness
        // Order::destroy($id);
        // return response()->noContent();
    }
}

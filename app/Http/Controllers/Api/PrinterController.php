<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class PrinterController extends Controller
{
    public function print(string $id)
    {
        $order = Order::with('orderItems')->findOrFail($id);

        try {
            // Send to Python Flask Bridge
            $response = Http::post('http://localhost:8800/print/order', $order->toArray());

            if ($response->successful()) {
                return response()->json(['message' => 'Print job sent successfully']);
            } else {
                return response()->json([
                    'message' => 'Printer bridge error',
                    'error' => $response->json(),
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to connect to printer bridge',
                'error' => $e->getMessage(),
            ], 503);
        }
    }
}

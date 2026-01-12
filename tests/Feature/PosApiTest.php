<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PosApiTest extends TestCase
{
    // Use RefreshDatabase to reset DB after each test
    // But since I am on a possibly persistent DB Env, I should be careful.
    // However, for standard Laravel tests it uses memory SQLite or separate DB if configured.
    // Given the user environment, I will NOT use RefreshDatabase to avoid wiping their actual dev DB if not configured correctly.
    // Instead I'll just clean up created data at the end or just let it be as "test data".
    // Better: Helper cleanup.

    public function test_can_create_and_list_menu_items()
    {
        $response = $this->postJson('/api/menu-items', [
            'name' => 'Test Nasi Goreng',
            'price' => 25000,
            'image_asset' => 'rice'
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'name' => 'Test Nasi Goreng',
                'price' => 25000,
            ]);

        $id = $response->json('id');

        $listResponse = $this->getJson('/api/menu-items');
        $listResponse->assertStatus(200);

        // Clean up
        MenuItem::destroy($id);
    }

    public function test_can_create_order_with_items_and_calculations()
    {
        // 1. Create Menu Items
        $item1 = MenuItem::create(['name' => 'Item 1', 'price' => 10000, 'image_asset' => 'img1']);
        $item2 = MenuItem::create(['name' => 'Item 2', 'price' => 20000, 'image_asset' => 'img2']);

        // 2. Create Order
        $orderData = [
            'payment_method' => 'QRIS',
            'items' => [
                ['id' => $item1->id, 'quantity' => 2],  // 20000
                ['id' => $item2->id, 'quantity' => 1],  // 20000
            ]
        ];
        // Expected Subtotal: 40000
        // Expected Tax (10%): 4000
        // Expected Total: 44000

        $response = $this->postJson('/api/orders', $orderData);

        $response
            ->assertStatus(201)
            ->assertJson([
                'subtotal' => 40000,
                'tax_amount' => 4000,
                'total_amount' => 44000,
                'payment_method' => 'QRIS',
                'payment_status' => 'PAID',
            ]);

        // Verify Order Items in DB/Response
        $orderId = $response->json('id');
        $this->assertDatabaseHas('order_items', [
            'order_id' => $orderId,
            'menu_item_id' => $item1->id,
            'quantity' => 2,
            'subtotal' => 20000
        ]);

        // Clean up
        Order::destroy($orderId);
        $item1->delete();
        $item2->delete();
    }
}

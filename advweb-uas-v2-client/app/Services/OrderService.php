<?php

namespace App\Services;

class OrderService extends ApiService
{
    /**
     * Get all orders with optional filters
     */
    public function getAll(array $filters = []): array
    {
        return $this->get('orders', $filters);
    }

    /**
     * Get a single order
     */
    public function find(int $id): array
    {
        return $this->get("orders/{$id}");
    }

    /**
     * Create a new order
     */
    public function create(array $data): array
    {
        return $this->post('orders', $data);
    }

    /**
     * Print an order
     */
    public function print(int $id): array
    {
        return $this->post("orders/{$id}/print");
    }
}

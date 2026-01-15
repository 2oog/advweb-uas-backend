<?php

namespace App\Services;

class MenuService extends ApiService
{
    /**
     * Get all menu items
     */
    public function getAll(): array
    {
        return $this->get('menu-items');
    }

    /**
     * Get a single menu item
     */
    public function find(int $id): array
    {
        return $this->get("menu-items/{$id}");
    }

    /**
     * Create a new menu item
     */
    public function create(array $data, $imageFile = null): array
    {
        if ($imageFile) {
            $multipart = [
                ['name' => 'name', 'contents' => $data['name']],
                ['name' => 'price', 'contents' => $data['price']],
                [
                    'name' => 'image',
                    'contents' => fopen($imageFile->getPathname(), 'r'),
                    'filename' => $imageFile->getClientOriginalName(),
                ],
            ];
            return $this->postMultipart('menu-items', $multipart);
        }

        return $this->post('menu-items', array_merge($data, [
            'image_asset' => $data['image_asset'] ?? 'utensils',
        ]));
    }

    /**
     * Update a menu item
     */
    public function update(int $id, array $data, $imageFile = null): array
    {
        if ($imageFile) {
            $multipart = [
                ['name' => '_method', 'contents' => 'PUT'],
                ['name' => 'name', 'contents' => $data['name']],
                ['name' => 'price', 'contents' => $data['price']],
                [
                    'name' => 'image',
                    'contents' => fopen($imageFile->getPathname(), 'r'),
                    'filename' => $imageFile->getClientOriginalName(),
                ],
            ];
            return $this->postMultipart("menu-items/{$id}", $multipart);
        }

        return $this->put("menu-items/{$id}", $data);
    }

    /**
     * Delete a menu item
     */
    public function destroy(int $id): array
    {
        return $this->delete("menu-items/{$id}");
    }
}

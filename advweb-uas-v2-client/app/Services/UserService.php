<?php

namespace App\Services;

class UserService extends ApiService
{
    /**
     * Get all users
     */
    public function getAll(): array
    {
        return $this->get('users');
    }

    /**
     * Create a new user
     */
    public function create(array $data): array
    {
        return $this->post('users', $data);
    }

    /**
     * Delete a user
     */
    public function destroy(int $id): array
    {
        return $this->delete("users/{$id}");
    }
}

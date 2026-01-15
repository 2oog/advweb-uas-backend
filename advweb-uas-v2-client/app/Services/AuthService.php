<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class AuthService extends ApiService
{
    /**
     * Login and retrieve API token
     */
    public function login(string $email, string $password): array
    {
        $response = $this->post('login', [
            'email' => $email,
            'password' => $password,
        ], false);

        // Backend returns 'access_token', not 'token'
        if ($response['success'] && isset($response['data']['access_token'])) {
            $this->setToken($response['data']['access_token']);
            
            // Store user data from response
            if (isset($response['data']['data'])) {
                Session::put('user', $response['data']['data']);
            } else {
                // Fetch user data separately if not included  
                $userResponse = $this->getCurrentUser();
                if ($userResponse['success']) {
                    Session::put('user', $userResponse['data']);
                }
            }
        }

        return $response;
    }

    /**
     * Logout and invalidate token
     */
    public function logout(): array
    {
        $response = $this->post('logout');
        $this->clearToken();
        
        return $response;
    }

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): array
    {
        return $this->get('user');
    }

    /**
     * Update password
     */
    public function updatePassword(string $currentPassword, string $newPassword, string $newPasswordConfirmation): array
    {
        return $this->put('password', [
            'current_password' => $currentPassword,
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPasswordConfirmation,
        ]);
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return Session::has('api_token') && Session::has('user');
    }

    /**
     * Get user from session
     */
    public function getUser(): ?array
    {
        return Session::get('user');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        $user = $this->getUser();
        return $user && ($user['role'] ?? '') === 'admin';
    }
}

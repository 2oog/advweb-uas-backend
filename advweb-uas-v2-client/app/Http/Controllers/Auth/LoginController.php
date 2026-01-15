<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // If already logged in, redirect to home
        if ($this->authService->isAuthenticated()) {
            return redirect('/');
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $response = $this->authService->login($credentials['email'], $credentials['password']);

        if ($response['success']) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => $response['error'] ?? 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $this->authService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

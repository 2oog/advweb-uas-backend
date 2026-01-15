<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Display settings page
     */
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Update password via API
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $response = $this->authService->updatePassword(
            $request->current_password,
            $request->new_password,
            $request->new_password_confirmation
        );

        if ($response['success']) {
            return back()->with('status', 'password-updated');
        }

        return back()->withErrors([
            'current_password' => $response['error'] ?? 'Failed to update password.',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display user management page
     */
    public function index()
    {
        $response = $this->userService->getAll();
        $users = $response['success'] ? $response['data'] : [];

        return view('admin.users.index', compact('users'));
    }

    /**
     * Store a new user
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,employee',
        ]);

        $response = $this->userService->create($data);

        if ($response['success']) {
            return back()->with('success', 'User created successfully.');
        }

        return back()->with('error', $response['error'] ?? 'Failed to create user.');
    }

    /**
     * Delete a user
     */
    public function destroy(int $id)
    {
        // Prevent deleting self
        $currentUser = Session::get('user');
        if ($currentUser && $currentUser['id'] == $id) {
            return back()->with('error', 'You cannot delete yourself!');
        }

        $response = $this->userService->destroy($id);

        if ($response['success']) {
            return back()->with('success', 'User deleted successfully.');
        }

        return back()->with('error', $response['error'] ?? 'Failed to delete user.');
    }
}

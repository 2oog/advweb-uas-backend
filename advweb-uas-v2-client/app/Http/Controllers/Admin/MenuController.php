<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    protected MenuService $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * Store a new menu item
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $imageFile = $request->hasFile('image') ? $request->file('image') : null;

        $response = $this->menuService->create($data, $imageFile);

        if ($request->expectsJson()) {
            if ($response['success']) {
                return response()->json(['success' => true, 'data' => $response['data']]);
            }
            return response()->json(['success' => false, 'message' => $response['error']], 400);
        }

        if ($response['success']) {
            return back()->with('success', 'Menu item created successfully.');
        }

        return back()->with('error', $response['error'] ?? 'Failed to create menu item.');
    }

    /**
     * Update a menu item
     */
    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $imageFile = $request->hasFile('image') ? $request->file('image') : null;

        $response = $this->menuService->update($id, $data, $imageFile);

        if ($request->expectsJson()) {
            if ($response['success']) {
                return response()->json(['success' => true, 'data' => $response['data']]);
            }
            return response()->json(['success' => false, 'message' => $response['error']], 400);
        }

        if ($response['success']) {
            return back()->with('success', 'Menu item updated successfully.');
        }

        return back()->with('error', $response['error'] ?? 'Failed to update menu item.');
    }

    /**
     * Delete a menu item
     */
    public function destroy(Request $request, int $id)
    {
        $response = $this->menuService->destroy($id);

        if ($request->expectsJson()) {
            if ($response['success']) {
                return response()->json(['success' => true]);
            }
            return response()->json(['success' => false, 'message' => $response['error']], 400);
        }

        if ($response['success']) {
            return back()->with('success', 'Menu item deleted successfully.');
        }

        return back()->with('error', $response['error'] ?? 'Failed to delete menu item.');
    }
}

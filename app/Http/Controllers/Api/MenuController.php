<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MenuItem::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048', // Allow file upload
            'image_asset' => 'nullable|string',     // Allow string fallback
        ]);

        $imagePath = $request->input('image_asset'); // Default to string input if any

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Move to base_path('images') as requested
            $file->move(base_path('images'), $filename);
            $imagePath = 'images/' . $filename;
        }

        $menuItem = MenuItem::create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'image_asset' => $imagePath,
        ]);

        return response()->json($menuItem, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return MenuItem::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $menuItem = MenuItem::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|integer|min:0',
            'image' => 'nullable|image|max:2048',
            'image_asset' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if it exists and is a file
            if ($menuItem->image_asset && file_exists(base_path($menuItem->image_asset)) && is_file(base_path($menuItem->image_asset))) {
                unlink(base_path($menuItem->image_asset));
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(base_path('images'), $filename);
            $validated['image_asset'] = 'images/' . $filename;
        }

        $menuItem->update($validated);

        return $menuItem;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $menuItem = MenuItem::findOrFail($id);
        
        // Delete image if exists
        if ($menuItem->image_asset && file_exists(base_path($menuItem->image_asset)) && is_file(base_path($menuItem->image_asset))) {
            unlink(base_path($menuItem->image_asset));
        }

        $menuItem->delete();

        return response()->noContent();
    }
}
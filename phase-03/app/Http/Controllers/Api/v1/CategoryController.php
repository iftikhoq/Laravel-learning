<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = array_values(Category::mockData());

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully.',
            'data' => $categories,
            'meta' => [
                'total' => count($categories),
            ],
        ], 200);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Category details retrieved successfully.',
            'data' => $category,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = new Category();
        $category->forceFill(array_merge(['id' => rand(10, 99)], $validated));

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully (Phase 2 scaffold).',
            'data' => $category,
        ], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->forceFill($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully (Phase 2 scaffold).',
            'data' => $category,
        ], 200);
    }

    public function destroy(Category $category): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => "Category ID {$category->id} deleted successfully.",
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->with('parent')
            ->whereNull('parent_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
            'meta' => [
                'total' => $categories->count(),
            ],
            'errors' => null,
        ], 200);
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadMissing('parent');

        return response()->json([
            'success' => true,
            'message' => 'Category details retrieved successfully.',
            'data' => new CategoryResource($category),
            'errors' => null,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => new CategoryResource($category),
            'errors' => null,
        ], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => new CategoryResource($category),
            'errors' => null,
        ], 200);
    }

    public function destroy(Category $category): JsonResponse
    {
        $id = $category->id;
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => "Category ID {$id} deleted successfully.",
            'data' => null,
            'errors' => null,
        ], 200);
    }
}

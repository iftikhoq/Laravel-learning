<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = array_values(Product::mockData());

        if ($categoryId = $request->query('category_id')) {
            $products = array_values(array_filter($products, fn ($p) => $p['category_id'] == $categoryId));
        }

        return response()->json([
            'success' => true,
            'message' => 'Product catalog retrieved successfully.',
            'data' => $products,
            'meta' => [
                'total' => count($products),
                'filtered_by_category' => $categoryId ?? null,
            ],
        ], 200);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Product details retrieved successfully.',
            'data' => $product,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $product = new Product();
        $product->forceFill(array_merge(['id' => rand(200, 999)], $validated));

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully (Phase 2 scaffold).',
            'data' => $product,
        ], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $product->forceFill($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully (Phase 2 scaffold).',
            'data' => $product,
        ], 200);
    }

    public function destroy(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => "Product SKU {$product->sku} (ID: {$product->id}) deleted successfully.",
        ], 200);
    }
}

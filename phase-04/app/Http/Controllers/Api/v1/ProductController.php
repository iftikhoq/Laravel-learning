<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with(['category', 'brand', 'images', 'primaryImage']);

        if ($request->filled('category_id')) {
            $query->category($request->integer('category_id'));
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->integer('brand_id'));
        }

        if ($request->filled('search')) {
            $query->search($request->string('search')->trim());
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        $perPage = $request->integer('per_page', 15);
        $products = $query->latest('id')->paginate($perPage)->appends($request->query());

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
            'errors' => null,
        ], 200);
    }

    public function show(Product $product): JsonResponse
    {
        $product->loadMissing(['category', 'brand', 'images', 'primaryImage']);

        return response()->json([
            'success' => true,
            'message' => 'Product details retrieved successfully.',
            'data' => new ProductResource($product),
            'errors' => null,
        ], 200);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $uploadedImages = $request->file('images', []);
            unset($validated['images']);

            $product = Product::create($validated);

            if (! empty($uploadedImages)) {
                $sortOrder = 1;
                foreach ($uploadedImages as $index => $file) {
                    $path = Storage::disk('public')->putFile('products', $file);
                    $product->images()->create([
                        'image_url' => $path,
                        'is_primary' => $index === 0,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }

            return $product;
        });

        $product->load(['category', 'brand', 'images', 'primaryImage']);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully with gallery images.',
            'data' => new ProductResource($product),
            'errors' => null,
        ], 201);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = DB::transaction(function () use ($request, $product) {
            $validated = $request->validated();
            $uploadedImages = $request->file('images', []);
            unset($validated['images']);

            $product->update($validated);

            if (! empty($uploadedImages)) {
                $currentMax = $product->images()->max('sort_order') ?? 0;
                $hasPrimary = $product->images()->where('is_primary', true)->exists();

                foreach ($uploadedImages as $index => $file) {
                    $path = Storage::disk('public')->putFile('products', $file);
                    $product->images()->create([
                        'image_url' => $path,
                        'is_primary' => ! $hasPrimary && $index === 0,
                        'sort_order' => ++$currentMax,
                    ]);
                }
            }

            return $product;
        });

        $product->load(['category', 'brand', 'images', 'primaryImage']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => new ProductResource($product),
            'errors' => null,
        ], 200);
    }

    public function destroy(Product $product): JsonResponse
    {
        $sku = $product->sku;
        $id = $product->id;

        DB::transaction(function () use ($product) {
            foreach ($product->images as $img) {
                if (Storage::disk('public')->exists($img->image_url)) {
                    Storage::disk('public')->delete($img->image_url);
                }
            }
            $product->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "Product SKU {$sku} (ID: {$id}) deleted successfully.",
            'data' => null,
            'errors' => null,
        ], 200);
    }
}

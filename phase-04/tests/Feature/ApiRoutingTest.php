<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_health_check_returns_200(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Laravel 11 API Service is online.',
            ]);
    }

    public function test_can_list_categories(): void
    {
        Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Gadgets and hardware',
        ]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'slug', 'description'],
                ],
                'meta' => ['total'],
                'errors',
            ]);
    }

    public function test_category_route_model_binding(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $category->id,
                    'name' => 'Electronics',
                ],
            ]);
    }

    public function test_product_catalog_and_route_model_binding(): void
    {
        $category = Category::create(['name' => 'Audio', 'slug' => 'audio']);
        $brand = Brand::create(['name' => 'Sony', 'slug' => 'sony']);

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Noise Canceling Headphones',
            'sku' => 'AUD-WNC-01',
            'price' => 199.99,
            'stock_quantity' => 10,
        ]);

        $listResponse = $this->getJson('/api/v1/products');
        $listResponse->assertStatus(200)
            ->assertJsonStructure(['success', 'data', 'meta', 'errors']);

        $detailResponse = $this->getJson("/api/v1/products/{$product->id}");
        $detailResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'sku' => 'AUD-WNC-01',
                ],
            ]);
    }

    public function test_missing_product_returns_404(): void
    {
        $response = $this->getJson('/api/v1/products/9999');
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Requested resource not found.',
                'errors' => null,
            ]);
    }
}

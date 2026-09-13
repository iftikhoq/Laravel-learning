<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiRoutingTest extends TestCase
{
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
        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'slug', 'description'],
                ],
                'meta' => ['total'],
            ]);
    }

    public function test_category_route_model_binding(): void
    {
        $response = $this->getJson('/api/v1/categories/1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 1,
                    'name' => 'Electronics',
                ],
            ]);
    }

    public function test_product_catalog_and_route_model_binding(): void
    {
        $listResponse = $this->getJson('/api/v1/products');
        $listResponse->assertStatus(200);

        $detailResponse = $this->getJson('/api/v1/products/101');
        $detailResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => 101,
                    'sku' => 'AUD-WNC-01',
                ],
            ]);
    }

    public function test_missing_product_returns_404(): void
    {
        $response = $this->getJson('/api/v1/products/9999');
        $response->assertStatus(404);
    }
}

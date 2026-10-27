<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Audio & Headphones',
            'slug' => 'audio-headphones',
            'description' => 'High quality audio gear',
            'is_active' => true,
        ]);

        $this->brand = Brand::create([
            'name' => 'SonicAudio',
            'slug' => 'sonic-audio',
            'logo_url' => 'https://example.com/logo.png',
            'is_active' => true,
        ]);
    }

    public function test_form_request_intercepts_invalid_submissions_and_returns_standardized_422_envelope(): void
    {
        $response = $this->postJson('/api/v1/products', [
            'name' => '',
            'category_id' => 9999, // non-existent
            'brand_id' => 9999, // non-existent
            'sku' => 'invalid sku format',
            'price' => -10,
            'sale_price' => 50,
            'stock_quantity' => -5,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The given data was invalid.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'name',
                    'category_id',
                    'brand_id',
                    'sku',
                    'price',
                    'stock_quantity',
                ],
            ]);
    }

    public function test_can_create_product_with_multi_image_gallery_uploads_and_public_urls(): void
    {
        Storage::fake('public');

        $image1 = UploadedFile::fake()->image('front.jpg', 600, 600);
        $image2 = UploadedFile::fake()->image('back.png', 600, 600);

        $payload = [
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'name' => 'Wireless ANC Headphones',
            'sku' => 'AUD-WNC-01',
            'description' => 'Flagship noise-canceling headphones',
            'price' => 299.99,
            'sale_price' => 249.99,
            'stock_quantity' => 50,
            'is_active' => true,
            'is_featured' => true,
            'images' => [$image1, $image2],
        ];

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully with gallery images.',
                'errors' => null,
            ])
            ->assertJsonPath('data.sku', 'AUD-WNC-01')
            ->assertJsonPath('data.price', 299.99)
            ->assertJsonPath('data.sale_price', 249.99)
            ->assertJsonPath('data.category.id', $this->category->id)
            ->assertJsonPath('data.brand.id', $this->brand->id)
            ->assertJsonCount(2, 'data.images');

        $responseData = $response->json('data');
        $this->assertNotEmpty($responseData['slug']);

        $imagePath1 = $responseData['images'][0]['image_url'];
        $imagePath2 = $responseData['images'][1]['image_url'];

        Storage::disk('public')->assertExists($imagePath1);
        Storage::disk('public')->assertExists($imagePath2);

        $this->assertTrue($responseData['images'][0]['is_primary']);
        $this->assertFalse($responseData['images'][1]['is_primary']);
        $this->assertStringContainsString('/storage/products/', $responseData['images'][0]['url']);
    }

    public function test_image_validation_rejects_non_image_files_and_excessive_images(): void
    {
        Storage::fake('public');

        $fakePdf = UploadedFile::fake()->create('manual.pdf', 500, 'application/pdf');

        $payload = [
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'name' => 'Audio Cable',
            'sku' => 'CAB-AUX-01',
            'price' => 19.99,
            'stock_quantity' => 100,
            'images' => [$fakePdf],
        ];

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure([
                'errors' => ['images.0'],
            ]);
    }

    public function test_can_list_products_with_standardized_envelope_and_pagination_metadata(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Product::create([
                'category_id' => $this->category->id,
                'brand_id' => $this->brand->id,
                'name' => "Product {$i}",
                'sku' => "PROD-ITEM-0{$i}",
                'price' => 10.00 * $i,
                'stock_quantity' => 10 * $i,
                'is_active' => true,
            ]);
        }

        $response = $this->getJson('/api/v1/products?per_page=3&page=1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'errors' => null,
            ])
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 3)
            ->assertJsonPath('meta.total', 10)
            ->assertJsonPath('meta.last_page', 4)
            ->assertJsonPath('meta.from', 1)
            ->assertJsonPath('meta.to', 3);
    }

    public function test_can_filter_products_by_search_category_and_active_status(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'name' => 'Special Gaming Headset',
            'sku' => 'GAM-HEAD-01',
            'price' => 149.99,
            'stock_quantity' => 20,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'name' => 'Office Ergonomic Chair',
            'sku' => 'FURN-CHR-01',
            'price' => 249.99,
            'stock_quantity' => 5,
            'is_active' => false,
        ]);

        $searchResponse = $this->getJson('/api/v1/products?search=Gaming');
        $searchResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'GAM-HEAD-01');

        $activeResponse = $this->getJson('/api/v1/products?is_active=1');
        $activeResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'GAM-HEAD-01');
    }

    public function test_can_show_single_product_in_standardized_envelope(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'name' => 'Studio Pro Mic',
            'sku' => 'MIC-STUD-01',
            'price' => 189.00,
            'stock_quantity' => 15,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product details retrieved successfully.',
                'errors' => null,
                'data' => [
                    'id' => $product->id,
                    'sku' => 'MIC-STUD-01',
                    'name' => 'Studio Pro Mic',
                    'category' => [
                        'id' => $this->category->id,
                        'name' => 'Audio & Headphones',
                    ],
                    'brand' => [
                        'id' => $this->brand->id,
                        'name' => 'SonicAudio',
                    ],
                ],
            ]);
    }

    public function test_can_update_product_and_append_gallery_images(): void
    {
        Storage::fake('public');

        $product = Product::create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'name' => 'Original Name',
            'sku' => 'ORIG-ITEM-01',
            'price' => 100.00,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $newImage = UploadedFile::fake()->image('extra.jpg', 400, 400);

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Name',
            'price' => 120.00,
            'images' => [$newImage],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product updated successfully.',
                'errors' => null,
            ])
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.price', 120)
            ->assertJsonCount(1, 'data.images');

        $imagePath = $response->json('data.images.0.image_url');
        Storage::disk('public')->assertExists($imagePath);
    }

    public function test_can_delete_product_and_associated_storage_images(): void
    {
        Storage::fake('public');

        $product = Product::create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'name' => 'Product To Delete',
            'sku' => 'DEL-PROD-01',
            'price' => 50.00,
            'stock_quantity' => 10,
        ]);

        $fakeImagePath = 'products/sample_delete.jpg';
        Storage::disk('public')->put($fakeImagePath, 'image binary content');

        $product->images()->create([
            'image_url' => $fakeImagePath,
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        $this->assertTrue(Storage::disk('public')->exists($fakeImagePath));

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'errors' => null,
            ]);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertFalse(Storage::disk('public')->exists($fakeImagePath));
    }

    public function test_missing_product_returns_standardized_404_envelope(): void
    {
        $response = $this->getJson('/api/v1/products/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Requested resource not found.',
                'errors' => null,
            ]);
    }
}

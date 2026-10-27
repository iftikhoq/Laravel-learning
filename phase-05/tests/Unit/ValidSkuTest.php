<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Rules\ValidSku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidSkuTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_sku_formats_pass_validation(): void
    {
        $validSkus = [
            'AUD-WNC-01',
            'KEY-ERG-02',
            'APP-HOOD-03',
            'TECH-PHN-999',
            'CAT-PROD-A1',
        ];

        foreach ($validSkus as $sku) {
            $validator = Validator::make(['sku' => $sku], [
                'sku' => [new ValidSku()],
            ]);

            $this->assertTrue($validator->passes(), "SKU '{$sku}' should be valid.");
        }
    }

    public function test_invalid_sku_formats_fail_validation(): void
    {
        $invalidSkus = [
            'lowercase-sku-01',
            'NOHYPHENS',
            'SPECIAL#CHARS-01',
            'A-1', // segments too short
            'TOOLONGSEGMENT12345-01',
            'trailing-hyphen-',
            '-leading-hyphen',
            'SPACES IN SKU-01',
        ];

        foreach ($invalidSkus as $sku) {
            $validator = Validator::make(['sku' => $sku], [
                'sku' => [new ValidSku()],
            ]);

            $this->assertFalse($validator->passes(), "SKU '{$sku}' should be rejected.");
        }
    }

    public function test_duplicate_sku_fails_validation(): void
    {
        $category = Category::create(['name' => 'Tech', 'slug' => 'tech']);
        $brand = Brand::create(['name' => 'Sony', 'slug' => 'sony']);

        Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Existing Product',
            'sku' => 'EXIST-PROD-01',
            'price' => 99.99,
            'stock_quantity' => 10,
        ]);

        $validator = Validator::make(['sku' => 'EXIST-PROD-01'], [
            'sku' => [new ValidSku()],
        ]);

        $this->assertFalse($validator->passes());
        $this->assertStringContainsString('already assigned', $validator->errors()->first('sku'));
    }

    public function test_sku_validation_allows_same_sku_when_ignoring_own_id(): void
    {
        $category = Category::create(['name' => 'Tech', 'slug' => 'tech']);
        $brand = Brand::create(['name' => 'Sony', 'slug' => 'sony']);

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Existing Product',
            'sku' => 'EXIST-PROD-01',
            'price' => 99.99,
            'stock_quantity' => 10,
        ]);

        $validator = Validator::make(['sku' => 'EXIST-PROD-01'], [
            'sku' => [new ValidSku($product->id)],
        ]);

        $this->assertTrue($validator->passes());
    }
}

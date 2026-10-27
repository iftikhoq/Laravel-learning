<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Seed Users for Testing Auth & Middleware
        // Admin user with full management privileges
        User::updateOrCreate(
            ['email' => 'admin@estore.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Active customer with standard user privileges
        User::updateOrCreate(
            ['email' => 'customer@estore.test'],
            [
                'name' => 'Active Customer',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'is_active' => true,
            ]
        );

        // Deactivated customer for testing EnsureCustomerIsActive middleware
        User::updateOrCreate(
            ['email' => 'inactive@estore.test'],
            [
                'name' => 'Deactivated Customer',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'is_active' => false,
            ]
        );

        // 2. Seed Categories
        $electronics = Category::firstOrCreate(
            ['slug' => 'electronics'],
            ['name' => 'Electronics', 'is_active' => true]
        );

        $audio = Category::firstOrCreate(
            ['slug' => 'audio'],
            ['name' => 'Audio & Headphones', 'parent_id' => $electronics->id, 'is_active' => true]
        );

        $wearables = Category::firstOrCreate(
            ['slug' => 'wearables'],
            ['name' => 'Smart Wearables', 'parent_id' => $electronics->id, 'is_active' => true]
        );

        $gaming = Category::firstOrCreate(
            ['slug' => 'gaming'],
            ['name' => 'Gaming Gear', 'parent_id' => $electronics->id, 'is_active' => true]
        );

        // 3. Seed Brands
        $apple = Brand::firstOrCreate(
            ['slug' => 'apple'],
            ['name' => 'Apple', 'is_active' => true]
        );

        $sony = Brand::firstOrCreate(
            ['slug' => 'sony'],
            ['name' => 'Sony', 'is_active' => true]
        );

        $bose = Brand::firstOrCreate(
            ['slug' => 'bose'],
            ['name' => 'Bose', 'is_active' => true]
        );

        // 4. Seed Products
        $demoProducts = [
            [
                'name' => 'Sony WH-1000XM5 Wireless Noise Canceling Headphones',
                'sku' => 'AUD-SNY-001',
                'category_id' => $audio->id,
                'brand_id' => $sony->id,
                'description' => 'Industry-leading noise cancellation with two processors and 8 microphones. Up to 30-hour battery life with quick charging.',
                'price' => 399.99,
                'sale_price' => 349.99,
                'stock_quantity' => 24,
                'is_featured' => true,
                'is_active' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=800&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Apple Watch Series 9 GPS 45mm Midnight',
                'sku' => 'WRB-APL-002',
                'category_id' => $wearables->id,
                'brand_id' => $apple->id,
                'description' => 'S9 SiP enables a super-illuminated display and a magical new Double Tap gesture to interact without touching the screen.',
                'price' => 429.00,
                'sale_price' => null,
                'stock_quantity' => 15,
                'is_featured' => true,
                'is_active' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=800&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Bose QuietComfort Ultra Wireless Noise Cancelling Headphones',
                'sku' => 'AUD-BOS-003',
                'category_id' => $audio->id,
                'brand_id' => $bose->id,
                'description' => 'World-class noise cancellation, quieter than ever before. Breakthrough spatialized audio for more immersive listening.',
                'price' => 429.00,
                'sale_price' => 379.00,
                'stock_quantity' => 8,
                'is_featured' => false,
                'is_active' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Apple AirPods Pro 2nd Gen with USB-C MagSafe Case',
                'sku' => 'AUD-APL-004',
                'category_id' => $audio->id,
                'brand_id' => $apple->id,
                'description' => 'Up to 2x more Active Noise Cancellation than the previous generation, with Transparency mode and Adaptive Audio.',
                'price' => 249.00,
                'sale_price' => 199.99,
                'stock_quantity' => 45,
                'is_featured' => true,
                'is_active' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=800&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'PlayStation 5 DualSense Wireless Controller Midnight Black',
                'sku' => 'GAM-SNY-005',
                'category_id' => $gaming->id,
                'brand_id' => $sony->id,
                'description' => 'Discover a deeper gaming experience with the innovative PS5 controller, featuring haptic feedback and dynamic triggers.',
                'price' => 74.99,
                'sale_price' => null,
                'stock_quantity' => 30,
                'is_featured' => false,
                'is_active' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1606318801954-d46d46d3360a?w=800&auto=format&fit=crop&q=80',
                ],
            ],
        ];

        foreach ($demoProducts as $item) {
            $images = $item['images'];
            unset($item['images']);

            $item['slug'] = Str::slug($item['name']);
            $product = Product::updateOrCreate(['sku' => $item['sku']], $item);

            foreach ($images as $idx => $url) {
                $product->images()->firstOrCreate(
                    ['image_url' => $url],
                    ['is_primary' => $idx === 0, 'sort_order' => $idx + 1]
                );
            }
        }
    }
}

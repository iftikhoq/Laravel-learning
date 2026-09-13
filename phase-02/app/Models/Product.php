<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['id', 'name', 'sku', 'price', 'stock', 'category_id', 'description'];

    public static function mockData(): array
    {
        return [
            101 => [
                'id' => 101,
                'name' => 'Wireless Noise-Canceling Headphones',
                'sku' => 'AUD-WNC-01',
                'price' => 199.99,
                'stock' => 45,
                'category_id' => 1,
                'description' => 'High-fidelity wireless headphones with active noise cancellation.',
            ],
            102 => [
                'id' => 102,
                'name' => 'Ergonomic Mechanical Keyboard',
                'sku' => 'KEY-ERG-02',
                'price' => 129.50,
                'stock' => 28,
                'category_id' => 1,
                'description' => 'Customizable RGB mechanical keyboard with tactile switches.',
            ],
            103 => [
                'id' => 103,
                'name' => 'Organic Cotton Hoodie',
                'sku' => 'APP-HOOD-03',
                'price' => 59.99,
                'stock' => 60,
                'category_id' => 2,
                'description' => 'Premium heavy-weight organic cotton hoodie in classic grey.',
            ],
        ];
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $mock = self::mockData();

        if (array_key_exists((int) $value, $mock)) {
            $data = $mock[(int) $value];
            $product = new static();
            $product->forceFill($data);
            return $product;
        }

        abort(404, 'Product not found.');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['id', 'name', 'slug', 'description'];

    public static function mockData(): array
    {
        return [
            1 => [
                'id' => 1,
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Gadgets, devices, and electronic accessories.',
            ],
            2 => [
                'id' => 2,
                'name' => 'Apparel & Fashion',
                'slug' => 'apparel-fashion',
                'description' => 'Clothing, footwear, and style essentials.',
            ],
            3 => [
                'id' => 3,
                'name' => 'Home & Kitchen',
                'slug' => 'home-kitchen',
                'description' => 'Appliances, cookware, and home decoration.',
            ],
        ];
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $mock = self::mockData();

        if (array_key_exists((int) $value, $mock)) {
            $data = $mock[(int) $value];
            $category = new static();
            $category->forceFill($data);
            return $category;
        }

        abort(404, 'Category not found.');
    }
}

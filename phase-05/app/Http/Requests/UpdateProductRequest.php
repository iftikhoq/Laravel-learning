<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Rules\ValidSku;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product instanceof Product ? $product->id : (is_numeric($product) ? (int) $product : null);

        $price = $this->has('price') ? $this->input('price') : ($product instanceof Product ? $product->price : null);

        $salePriceRules = ['nullable', 'numeric', 'min:0'];
        if ($price !== null) {
            $salePriceRules[] = 'lt:' . $price;
        }

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'brand_id' => ['sometimes', 'required', 'integer', 'exists:brands,id'],
            'sku' => ['sometimes', 'required', 'string', new ValidSku($productId)],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'sale_price' => $salePriceRules,
            'stock_quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,png,webp,jpg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected product category does not exist.',
            'brand_id.exists' => 'The selected product brand does not exist.',
            'sale_price.lt' => 'The promotional sale price must be lower than the product price.',
            'images.max' => 'You cannot upload more than 5 gallery images per product.',
            'images.*.image' => 'Every uploaded gallery file must be a valid image file.',
            'images.*.mimes' => 'Images must be in jpeg, png, webp, or jpg format.',
            'images.*.max' => 'Each image size must not exceed 2048 kilobytes (2MB).',
        ];
    }
}

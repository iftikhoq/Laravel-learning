<?php

namespace App\Http\Requests;

use App\Rules\ValidSku;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'sku' => ['required', 'string', new ValidSku()],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
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
            'sale_price.lt' => 'The promotional sale price must be lower than the original price.',
            'images.max' => 'You cannot upload more than 5 gallery images per product.',
            'images.*.image' => 'Every uploaded gallery file must be a valid image file.',
            'images.*.mimes' => 'Images must be in jpeg, png, webp, or jpg format.',
            'images.*.max' => 'Each image size must not exceed 2048 kilobytes (2MB).',
        ];
    }
}

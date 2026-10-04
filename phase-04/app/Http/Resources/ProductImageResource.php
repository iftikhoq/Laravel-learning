<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $url = $this->image_url;
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = Storage::disk('public')->url($url);
        }

        return [
            'id' => $this->id,
            'image_url' => $this->image_url,
            'url' => $url,
            'is_primary' => (bool) $this->is_primary,
            'sort_order' => (int) $this->sort_order,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'logo' => $this->logoImage
                ? Storage::disk('custom')->url($this->logoImage->path)
                : null,
            // Banner
            'banners' => $this->bannerImages
                ? $this->bannerImages->map(
                    fn($img) => Storage::disk('custom')->url($img->path)
                )
                : [],
            'is_featured' => (bool) $this->is_featured,
            'status'      => $this->status,
            // 'products_count' => $this->whenLoaded('products', function () {
            //     return $this->products->count();
            // }),
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'  => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

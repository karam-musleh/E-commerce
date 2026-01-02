<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    protected $selectedValueIds = [];
    protected $additionalPrice = 0;

    public function __construct($resource, $selectedValueIds = [], $additionalPrice = 0)
    {
        parent::__construct($resource);
        $this->selectedValueIds = $selectedValueIds;
        $this->additionalPrice = $additionalPrice;
    }

    protected function formattedAttributes()
    {
        // ✅ تأكد من وجود attributeValues
        if (!$this->resource || !$this->resource->attributeValues) {
            return [];
        }

        return $this->resource->attributeValues
            ->groupBy(fn($val) => $val->attribute->name)
            ->map(function ($values, $attributeName) {
                return [
                    "attribute" => $attributeName,
                    "values" => $values->map(function ($val) {
                        return [
                            "id" => $val->id,
                            "value" => $val->value,
                            "slug" => $val->slug,
                            "additional_price" => $val->pivot->additional_price ?? 0,
                            "quantity" => $val->pivot->quantity ?? 0,
                            "min_qty" => $val->pivot->min_qty ?? 1,
                            "selected" => in_array($val->id, (array)$this->selectedValueIds)
                        ];
                    })->values()
                ];
            })
            ->values();
    }

    public function toArray(Request $request): array
    {
        // ✅ تأكد من وجود الـ resource
        if (!$this->resource) {
            return [];
        }

        return [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "description" => $this->description,

            "images" => [
                "main" => $this->main_image_url,
                "gallery" => $this->when(
                    $this->relationLoaded('galleryImages'),
                    fn() => $this->gallery_images_urls
                ),
            ],

            'category' => $this->when(
                $this->relationLoaded('category') && $this->category,
                fn() => [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ]
            ),

            "brand" => $this->when(
                $this->relationLoaded('brand') && $this->brand,
                fn() => [
                    "id" => $this->brand->id,
                    "name" => $this->brand->name,
                    "slug" => $this->brand->slug,
                ]
            ),

            'attributes' => $this->when(
                $this->relationLoaded('attributeValues') && $this->attributeValues->isNotEmpty(),
                fn() => $this->formattedAttributes()
            ),

            "pricing" => [
                "price" => $this->main_price ?? 0,
                "discount" => $this->discount,
                "discount_type" => $this->discount_type ?? 'percent',
                "final_price" => ($this->final_price ?? 0) + $this->additionalPrice,
            ],

            "quantity" => $this->total_quantity ?? 0,
            // "min_qty" => $this->min_qty,
            "status" => $this->status,
            "unit" => $this->unit,
            "weight" => $this->weight,

            "deal" => $this->when(
                $this->relationLoaded('dailyDeal') && $this->dailyDeal,
                fn() => new DailyDealResource($this->dailyDeal)
            ),

            "rating" => [
                "average" => round($this->average_rating ?? 0, 1),
                "count" => $this->reviews_count ?? 0,
            ],

            "created_at" => $this->created_at?->format('Y-m-d H:i:s'),
            "updated_at" => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

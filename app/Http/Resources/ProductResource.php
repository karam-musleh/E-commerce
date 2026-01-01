<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    protected $selectedValueIds = [];
    protected $additionalPrice = 0;
    public function __construct($resource, $selectedValueIds = [], $additionalPrice = 0)
    {
        parent::__construct($resource);

        $this->selectedValueIds = $selectedValueIds;
        $this->additionalPrice = $additionalPrice;
    }

    protected function formattedAttributes($product)
    {
        return $product->attributeValues
            ->groupBy(fn($val) => $val->attribute->name)
            ->map(function ($values, $attributeName) {
                return [
                    "attribute" => $attributeName,
                    "values" => $values->map(function ($val) {
                        return [
                            "id" => $val->id,
                            "value" => $val->value,
                            "slug" => $val->slug,
                            "additional_price" => $val->pivot->additional_price,
                            "quantity" => $val->pivot->quantity,
                            "min_qty" => $val->pivot->min_qty,
                            "selected" => in_array($val->id, (array)$this->selectedValueIds)
                        ];
                    })->values()
                ];
            })
            ->values();
    }


    // public function toArray(Request $request): array
    // {


    //     return [
    //         "id"            => $this->id,
    //         "name"          => $this->name,
    //         "slug"          => $this->slug,
    //         "description"   => $this->description,
    //         "images" => [
    //             "main" => $this->main_image_url,
    //             "gallery" => $this->when(
    //                 $this->relationLoaded('galleryImages'),
    //                 fn() => $this->gallery_images_urls
    //             ),
    //         ],


    //         "category" => $this->when($this->category, fn() => [
    //             "id" => $this->category->id,
    //             "name" => $this->category->name,
    //             "slug" => $this->category->slug,
    //         ]),

    //         "brand" => $this->when($this->brand, fn() => [
    //             "id"   => $this->brand->id,
    //             "name" => $this->brand->name,
    //             "slug" => $this->brand->slug,
    //         ]),

    //         'attributes' => $this->formattedAttributes($this),
    //         // 'attribute_values' => $this->when(
    //         //     $this->relationLoaded('attributeValues') && $this->attributeValues->isNotEmpty(),
    //         //     fn() => $this->attributeValues->map(function ($value) {
    //         //         return [
    //         //             'id' => $value->id,
    //         //             'value' => $value->value,
    //         //             'slug' => $value->slug,
    //         //             'additional_price' => $value->pivot->additional_price,
    //         //         ];
    //         //     })
    //         // ),
    //         "pricing" => [
    //             "price"         => $this->main_price,
    //             "discount"      => $this->discount,
    //             "discount_type" => $this->discount_type,
    //             "final_price"   => $this->final_price,
    //         ],

    //         "quantity"      => $this->total_quantity,
    //         "status"        => $this->status,
    //         "unit"          => $this->unit,
    //         "weight"        => $this->weight,

    //         "created_at"    => $this->created_at,
    //         "updated_at"    => $this->updated_at,

    //         // "created_at" => $this->created_at->format('Y-m-d H:i:s'),
    //         // "updated_at" => $this->updated_at->format('Y-m-d H:i:s'),
    //     ];
    // }
    public function toArray(Request $request): array
    {
        return [
            "id"            => $this->id,
            "name"          => $this->name,
            "slug"          => $this->slug,
            "description"   => $this->description,

            "images" =>  [
                "main" => $this->main_image_url,
                "gallery" => $this->when(
                    $this->relationLoaded('galleryImages'),
                    fn() => $this->gallery_images_urls
                ),
            ],

            "category" => $this->when($this->category, fn() => [
                "id" => $this->category->id,
                "name" => $this->category->name,
                "slug" => $this->category->slug,
            ]),

            "brand" => $this->when($this->brand, fn() => [
                "id"   => $this->brand->id,
                "name" => $this->brand->name,
                "slug" => $this->brand->slug,
            ]),

            'attributes' => $this->when(
                $this->relationLoaded('attributeValues') && $this->attributeValues->isNotEmpty(),
                fn() => $this->formattedAttributes($this)
            ),


            "pricing" => [
                "price"         => $this->main_price,
                "discount"      => $this->discount,
                "discount_type" => $this->discount_type,
                "final_price"   => $this->final_price + $this->additionalPrice,
            ],

            "quantity"      => $this->total_quantity,
            "status"        => $this->status,
            "unit"          => $this->unit,
            "weight"        => $this->weight,

            "deal" => $this->whenLoaded(
                'dailyDeal',
                fn() =>
                new DailyDealResource($this->dailyDeal)
            ),
            "rating" => [
                "average" => round($this->average_rating, 1),
                "count"   => $this->reviews_count,
            ],

            "created_at"    => $this->created_at,
            "updated_at"    => $this->updated_at,
        ];
    }
}

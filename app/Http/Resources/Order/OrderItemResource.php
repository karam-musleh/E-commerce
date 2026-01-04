<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // dd(
        //     $this->unit_price ,
        //     $this->total_price,
        //     $this->discount
        // );
        return [
            'id' => $this->id,

            // product snapshot
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'image' => $this->product->main_image_url,
            ],

            'quantity' => $this->quantity,

            'pricing' => [
                'unit_price' => $this->unit_price / 100,
                'total_price' => $this->total_price / 100,
                'discount' => $this->discount ,
                'discount_type' => $this->discount_type,
            ],

            // attributes snapshot
            'attributes' => $this->whenLoaded('itemAttributes', function () {
                return $this->itemAttributes->map(fn ($attr) => [
                    'name' => $attr->attribute_name,
                    'value' => $attr->attribute_value,
                    'additional_price' => $attr->additional_price,
                ]);
            }),
        ];
    }
}

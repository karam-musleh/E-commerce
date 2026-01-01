<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // معلومات المنتج
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'image' => $this->product->main_image_url,
            ],

            // الكمية
            'quantity' => $this->quantity,

            // الأسعار
            'pricing' => [
                'unit_price' => $this->unit_price / 100,
                'total_price' => $this->total_price / 100,
                'discount' => $this->discount,
                'discount_type' => $this->discount_type,
            ],

            // الـ Attributes
            'attributes' => $this->whenLoaded('attributes', function () {
                return $this->attributes->map(function ($attr) {
                    return [
                        'name' => $attr->attribute_name,
                        'value' => $attr->attribute_value,
                        'additional_price' => $attr->additional_price / 100,
                    ];
                });
            }),
        ];
    }
}

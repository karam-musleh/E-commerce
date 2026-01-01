<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request): array
    {
        $cartTotal = 0;

        $items = $this->items->map(function ($item) use (&$cartTotal) {
            $basePrice = $item->product->final_price;
            $additional = 0;

            foreach ($item->attributeValues as $attr) {
                $additional += $attr->pivot->additional_price ?? 0;
            }

            $itemTotal = ($basePrice + $additional) * $item->quantity;
            $cartTotal += $itemTotal;

            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => $basePrice,
                'additional_price' => $additional,
                'total_price' => $itemTotal,
                'attributes' => $item->attributeValues->map(function ($attr) {
                    return [
                        'id' => $attr->id,
                        'name' => $attr->attribute->name ?? null,
                        'value' => $attr->value,
                        'additional_price' => $attr->pivot->additional_price ?? 0,
                    ];
                }),
            ];
        });

        return [
            'items' => $items,
            'cart_total' => $cartTotal,
        ];
    }
}

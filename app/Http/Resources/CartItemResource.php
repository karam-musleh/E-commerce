<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {

        $basePrice = $this->product->final_price;
        $additional = 0;
        if ($this->attributeValues->isNotEmpty()) {

        foreach ($this->attributeValues as $attr) {
            $pivot = $this->product
                ->attributeValues
                ->where('id', $attr->id)
                ->first();

            $additional += $pivot?->pivot?->additional_price ?? 0;
        }
    }

        $totalPrice = ($basePrice + $additional) * $this->quantity;

        return [
            'product_id' => $this->product_id,
            'product_name' => $this->product->name,
            'quantity' => $this->quantity,
            'unit_price' => $basePrice,
            'additional_price' => $additional,
            'total_price' => $totalPrice,
            'attributes' => $this->attributeValues->map(function ($attr) {
                return [
                    'id' => $attr->id,
                    'name' => $attr->attribute->name ?? null,
                    'value' => $attr->value,
                    'additional_price' => $attr->pivot->additional_price ?? 0,
                ];
            }),
        ];
    }
}

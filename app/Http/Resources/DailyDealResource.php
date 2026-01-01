<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyDealResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "product" => new ProductResource(
                $this->whenLoaded('product')
            ),
            "discount" => $this->discount,
            "discount_type" => $this->discount_type,
            "starts_at" => $this->starts_at,
            "ends_at" => $this->ends_at,
            "status" => $this->state,
            "main_price" => $this->product?->main_price,
            // "final_price" => $this->final_price,

            // "is_active" => $this->is_active,
            "final_price" => $this->final_price,
            "is_active" => $this->is_active,

            // "isActive" => $this->isActive,
        ];
    }
}

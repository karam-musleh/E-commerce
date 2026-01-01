<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'product' => [
                'id' => $this->first()->product->id,
                'name' => $this->first()->product->name,
            ], 
            'reviews' => ReviewResource::collection($this->values()),
        ];
    }
}


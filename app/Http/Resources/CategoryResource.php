<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // total price = price + sum of additional_price of all selected attribute values - discount + tax


        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'parent_id'   => $this->parent_id,
            'icon'        => $this->icon ? asset('uploads/' . $this->icon) : null,
            'banner'      => $this->banner ? Storage::disk('uploads')->url($this->banner) : null,
            'image'       => $this->image ? Storage::disk('uploads')->url($this->image) : null,
            // 'price'      => [
            //     'main_price' => $this->price,
            //     'discount'   => $this->discount,
            //     'discount_type' => $this->discount_type,
            // ]
            'is_featured' => (bool) $this->is_featured,
            'status'   =>  $this->status,

            'children' => $this->when(
                ($this->relationLoaded('childrenAdmin') && $this->childrenAdmin->isNotEmpty())
                    || ($this->relationLoaded('children') && $this->children->isNotEmpty()),
                fn() => $this->relationLoaded('childrenAdmin') && $this->childrenAdmin->isNotEmpty()
                    ? CategoryResource::collection($this->childrenAdmin)
                    : CategoryResource::collection($this->children)
            ),
            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'  => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

<?php

namespace App\Traits;

use App\Models\Attribute;

trait AttributeHelperTrait
{
    protected function getAttributeBySlug(string $slug, bool $withValues = false): ?Attribute
    {
        $query = Attribute::where('slug', $slug);

        if ($withValues) {
            $query->with('values'); 
        }

        return $query->first();
    }


    protected function getAttributeValueBySlug(Attribute $attribute, string $valueSlug)
    {
        return $attribute->values()->where('slug', $valueSlug)->first();
    }
}

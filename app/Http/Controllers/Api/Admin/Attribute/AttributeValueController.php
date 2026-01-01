<?php

namespace App\Http\Controllers\Api\admin\Attribute;

use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttributeValueRequest;
use App\Http\Resources\AttributeValueResource;
use App\Traits\AttributeHelperTrait;

class AttributeValueController extends Controller
{
    use ApiResponserTrait, AttributeHelperTrait;

    //
    public function index($slug)
    {
        $attributeValues = $this->getAttributeBySlug($slug, true);
        if (!$attributeValues) {
            return $this->errorResponse('Attribute not found', 404);
        }
        return $this->successResponse(
            AttributeValueResource::collection($attributeValues->values),
            'Attribute values retrieved successfully',
            200
        );
    }
    
    public function store(AttributeValueRequest $request, $slug)
    {
        $attribute = $this->getAttributeBySlug($slug);
        if (!$attribute) {
            return $this->errorResponse('Attribute not found', 404);
        }

        $attributeValue = $attribute->values()->create($request->validated());
        return $this->successResponse(
            new AttributeValueResource($attributeValue),
            'Attribute value created successfully',
            201
        );
    }
    //
    public function update(AttributeValueRequest $request, $slug, $valueSlug)
    {
        $attribute = $this->getAttributeBySlug($slug);
        if (!$attribute) {
            return $this->errorResponse('Attribute not found', 404);
        }

        $attributeValue = $this->getAttributeValueBySlug($attribute, $valueSlug);
        if (!$attributeValue) {
            return $this->errorResponse('Attribute value not found', 404);
        }

        $attributeValue->update($request->validated());
        return $this->successResponse(
            $attributeValue,
            'Attribute value updated successfully',
            200
        );
    }

    public function destroy($slug, $valueSlug)
    {
        $attribute = $this->getAttributeBySlug($slug);
        if (!$attribute) {
            return $this->errorResponse('Attribute not found', 404);
        }

        $attributeValue = $this->getAttributeValueBySlug($attribute, $valueSlug);
        if (!$attributeValue) {
            return $this->errorResponse('Attribute value not found', 404);
        }

        $attributeValue->delete();
        return $this->successResponse(
            null,
            'Attribute value deleted successfully',
            200
        );
    }
}

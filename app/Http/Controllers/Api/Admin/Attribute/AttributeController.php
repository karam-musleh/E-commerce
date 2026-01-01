<?php

namespace App\Http\Controllers\Api\admin\Attribute;

use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttributeRequest;
use App\Http\Resources\AttributeResource;

class AttributeController extends Controller
{
    use ApiResponserTrait;
    //
    public function index()
    {
        $attributes = Attribute::paginate(5);
        return $this->successResponse(
            AttributeResource::collection($attributes),
            'Attributes retrieved successfully',
            200
        );
    }
    public function show($slug)
    {
        $attribute = Attribute::where('slug', $slug)
        ->with('values')
        ->first();

        if (!$attribute) {
            return $this->errorResponse('Attribute not found', 404);
        }
        return $this->successResponse(
            new AttributeResource($attribute),
            'Attribute retrieved successfully',
            200
        );
    }

    public function store(AttributeRequest $request)
    {
        $data = $request->validated();
        $attribute = Attribute::create($data);

        return $this->successResponse(
            new AttributeResource($attribute),
            'Attribute created successfully',
            201
        );

    }
    public function update(AttributeRequest $request, $slug)
    {
        $attribute = Attribute::where('slug', $slug)->first();
        if (!$attribute) {
            return $this->errorResponse('Attribute not found', 404);
        }
        $data = $request->validated();
        $attribute->update($data);

        return $this->successResponse(
            new AttributeResource($attribute),
            'Attribute updated successfully',
            200
        );
    }

    public function destroy($slug)
    {
        $attribute = Attribute::where('slug', $slug)->first();
        if (!$attribute) {
            return $this->errorResponse('Attribute not found', 404);
        }
        $attribute->delete();
        return $this->successResponse(
            null,
            'Attribute deleted successfully',
            200
        );
    }


}

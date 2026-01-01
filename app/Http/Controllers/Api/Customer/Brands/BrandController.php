<?php

namespace App\Http\Controllers\Api\Customer\Brands;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;

class BrandController extends Controller
{
    use ApiResponserTrait;

    //
    public function index()
    {
        $perPage = request()->get('per_page', 5);

        $brands = Brand::active()
            ->select('id', 'name', 'slug', 'status', 'is_featured')
            ->paginate($perPage);
        if ($brands->count() === 0) {
            return $this->errorResponse('No brands found', 404);
        }
        return $this->successResponse(
            BrandResource::collection($brands),
            'Brands retrieved successfully',
            200
        );
    }
    //
    public function show($slug)
    {
        $brand = Brand::where('slug', $slug)
            ->with('logoImage', 'bannerImages')
            ->active()
            ->first();
        if (!$brand) {
            return $this->errorResponse('Brand not found', 404);
        }
        return $this->successResponse(
            new BrandResource($brand),
            'Brand retrieved successfully',
            200
        );
    }
    public function featuredBrands()
    {
        $brands = Brand::featured()
            ->active()
            ->get();
        if ($brands->isEmpty()) {
            return $this->errorResponse('No featured brands found', 404);
        }
        return $this->successResponse(
            BrandResource::collection($brands),
            'Featured brands retrieved successfully',
            200
        );
    }
}

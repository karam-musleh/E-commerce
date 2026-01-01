<?php

namespace App\Http\Controllers\Api\Admin\Brand;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Requests\BrandRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Helpers\ImageHelper;
use Termwind\Components\Hr;

class BrandController extends Controller
{
    use ApiResponserTrait;

    public function index()
    {
        $brands = Brand::with(['logoImage', 'bannerImages'])->paginate(5);

        return $this->successResponse(
            BrandResource::collection($brands),
            'Brands retrieved successfully',
            200
        );
    }

    public function show($slug)
    {
        $brand = Brand::where('slug', $slug)->first();
        if (!$brand) return $this->errorResponse('Brand not found', 404);

        $brand->load(['logoImage', 'bannerImages']);
        return $this->successResponse(
            new BrandResource($brand),
            'Brand retrieved successfully',
            200
        );
    }

    public function store(BrandRequest $request)
    {
        $data = $request->validated();
        $brand = Brand::create($data);

        if ($request->hasFile('logo')) {
            ImageHelper::uploadImage($brand, $request->file('logo'), 'brands/logos', 'brand_logo');
        }

        if ($request->hasFile('banners')) {
            ImageHelper::uploadGallery($brand, $request->file('banners'), 'brands/banners', 'brand_banner');
        }

        return $this->successResponse(
            new BrandResource($brand->load(['logoImage', 'bannerImages'])),
            'Brand created successfully',
            201
        );
    }

    public function update(BrandRequest $request, $slug)
    {
        $brand = Brand::where('slug', $slug)->first();
        if (!$brand) {
            return $this->errorResponse('Brand not found', 404);
        }

        $brand->update($request->validated());
        if ($request->hasFile('logo')) {

            ImageHelper::updateImage($brand, $request->file('logo'), 'brands/logos', 'brand_logo');
        }
        if ($request->hasFile('banner') || $request->has('delete_banner_ids')) {
            ImageHelper::updateGallery(
                $brand,
                $request->file('banner', []),
                $request->input('delete_banner_ids', []),
                'brands/banners'                        
            );
        }


        return $this->successResponse(
            new BrandResource($brand->load(['logoImage', 'bannerImages'])),
            'Brand updated successfully',
            200
        );
    }

    public function destroy($slug)
    {
        $brand = Brand::where('slug', $slug)->first();
        if (!$brand) return $this->errorResponse('Brand not found', 404);

        ImageHelper::deleteAll($brand);
        $brand->delete();

        return $this->successResponse(null, 'Brand deleted successfully', 200);
    }
}

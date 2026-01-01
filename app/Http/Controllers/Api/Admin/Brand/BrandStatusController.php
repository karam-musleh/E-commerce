<?php

namespace App\Http\Controllers\Api\Admin\Brand;

use App\Models\Brand;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;

class BrandStatusController extends Controller
{
        use ApiResponserTrait;

    public function toggleFeatured($slug)
    {
        $brand = Brand::where('slug', $slug)->first();
        if (!$brand) {
            return $this->errorResponse('Brand not found', 404);
        }
        $brand->update([
            'is_featured' => !$brand->is_featured
        ]);
        return $this->successResponse(new BrandResource($brand), 'Featured status updated');
    }




    public function updateStatus(Request $request, $slug)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $brand = Brand::where('slug', $slug)->first();
        if (!$brand) {
            return $this->errorResponse('Brand not found', 404);
        }

        if (! $brand->setStatus($request->status)) {
            return $this->errorResponse('Invalid status provided.', 400);
        }
        return $this->successResponse(
            new BrandResource($brand),
            'Brand status updated successfully',
            200
        );
    }


    //
}

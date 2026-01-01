<?php

namespace App\Http\Controllers\Api\Customer\Category;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponserTrait;
use App\Http\Resources\CategoryResource;
use App\Models\Category;


class CategoryController extends Controller
{
    use ApiResponserTrait;

    public function index()
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->active()
            ->select('id', 'name', 'slug', 'parent_id', 'status')
            ->get();
        if ($categories->isEmpty()) {
            return $this->errorResponse('No categories found', 404);
        }

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Categories retrieved successfully',
            200
        );
    }

    public function show($slug)
    {
        $category = Category::with('children')
            ->where('slug', $slug)
            ->where('status', Category::STATUS_ACTIVE)
            ->first();

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        return $this->successResponse(
            new CategoryResource($category),
            'Category retrieved successfully',
            200
        );
    }
    // get category featured categories
    public function featuredCategories()
    {
        $categories = Category::where('is_featured', true)
            ->active()
            ->get();

        if ($categories->isEmpty()) {
            return $this->errorResponse('No featured categories found', 404);
        }

        return $this->successResponse(
            CategoryResource::collection($categories),
            'Featured categories retrieved successfully',
            200
        );
    }
}

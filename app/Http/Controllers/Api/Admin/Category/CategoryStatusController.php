<?php

namespace App\Http\Controllers\Api\Admin\Category;


use App\Models\Category;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;


class CategoryStatusController extends Controller
{
    use ApiResponserTrait;

    public function toggleFeatured($slug)
    {
        $category = Category::where('slug', $slug)->first();
        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }
        $category->update([
            'is_featured' => !$category->is_featured
        ]);
        return $this->successResponse(new CategoryResource($category), 'Featured status updated');
    }

    public function updateStatus(Request $request, Category $category)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        if (! $category->setStatus($request->status)) {
            return
                $this->errorResponse('Invalid status provided.', 400);
        }

        return $this->successResponse(
            new CategoryResource($category),
            'Category status updated successfully.'
        )
        ;
    }

    private function getSpecialCategories($column, $message)
    {
        $query = Category::where($column, true);
        $categories = $query->get();

        if ($categories->isEmpty()) {
            return $this->errorResponse("No {$message} categories found", 404);
        }

        return $this->successResponse(
            CategoryResource::collection($categories),
            ucfirst($message) . ' categories retrieved'
        );
    }

    public function featuredCategories()
    {
        return $this->getSpecialCategories('is_featured', 'featured');
    }
}

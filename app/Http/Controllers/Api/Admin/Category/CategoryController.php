<?php


namespace App\Http\Controllers\Api\Admin\Category;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Helpers\CategoryImageHelper;
use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CategoryResource;


class CategoryController extends Controller
{
    use ApiResponserTrait;


    public function index(Request $request)
    {
        $status = $request->query('status');

        $categories = Category::query()
            ->whereNull('parent_id')
            ->when(
                $status,
                fn($q) => $q->where('status', $status)
            )
            ->with('childrenAdmin')
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

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();

        $category = Category::create($data);

        foreach (['image', 'banner', 'icon'] as $type) {
            if ($request->hasFile($type)) {
                ImageHelper::uploadImage(
                    $category,
                    $request->file($type),
                    "categories/$type",
                    $type
                );
            }
        }

        return $this->successResponse(
            new CategoryResource($category),
            'Category created successfully',
            201
        );
    }


    public function show($slug)
    {
        $category = Category::with('childrenAdmin')
            ->where('slug', $slug)
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
    public function update(CategoryRequest $request, $slug)
    {
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        $data = $request->validated();


        foreach (['image', 'banner', 'icon'] as $type) {
            if ($request->hasFile($type)) {
                ImageHelper::updateImage(
                    $category,
                    $request->file($type),
                    "categories/$type",
                    $type
                );
            }
        }

        $category->update($data);

        return $this->successResponse(
            new CategoryResource($category),
            'Category updated successfully',
            200
        );
    }
    public function destroy($slug, Request $request)
    {
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }
        if ($request->has('children_reassign_to')) {
            $newParent = Category::where('id', $request->children_reassign_to)->first();
            foreach ($category->childrenAdmin as $child) {
                $child->update(['parent_id' => $newParent->id]);
            }
        } else {
            foreach ($category->childrenAdmin as $child) {
                ImageHelper::deleteAll($child);
                $child->delete();
            }
        }
        if ($request->has('products_reassign_to')) {
            $newCategory = Category::where('id', $request->products_reassign_to)->first();
            foreach ($category->products as $product) {
                $product->update(['category_id' => $newCategory->id]);
            }
        } else {

            foreach ($category->products as $product) {
                $product->delete();
            }
        }
        ImageHelper::deleteAll($category);

        $category->delete();


        return $this->successResponse(null, 'Category deleted successfully', 200);
    }
}

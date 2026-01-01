<?php

namespace App\Http\Controllers\Api\Admin\Product;

use App\Models\Product;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ProductController extends Controller
{
    use ApiResponserTrait;

    public function index(Request $request)
    {
        $orderBy = $request->query('order_by', 'main_price');
        $sort = $request->query('sort', 'asc');
        $perPage = $request->query('per_page', 10);

        $products = Product::select('id', 'name', 'slug', 'main_price', 'discount', 'discount_type')
            ->with([
                'category:id,name,slug',
                'brand:id,name,slug',
                'mainImage:id,imageable_id,imageable_type,path'
            ])
            ->when($request->query('min_price'), function (Builder $query, $minPrice) {
                $query->where('main_price', '>=', round($minPrice * 100));
            })
            ->when($request->query('max_price'), function (Builder $query, $maxPrice) {
                $query->where('main_price', '<=', round($maxPrice * 100));
            })
            ->when($request->query('q'), function (Builder $query) use ($request) {
                $search = $request->query('q');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%")
                        ->orWhere('slug', 'LIKE', "%$search%")
                        ->orWhereHas('category', function ($cat) use ($search) {
                            $cat->where('name', 'LIKE', "%$search%")
                                ->orWhere('slug', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('brand', function ($brand) use ($search) {
                            $brand->where('name', 'LIKE', "%$search%")
                                ->orWhere('slug', 'LIKE', "%$search%");
                        });
                });
            })
            ->orderBy($orderBy, $sort)
            ->paginate($perPage);

        if ($products->isEmpty()) {
            return $this->errorResponse("No products found", 404);
        }
        return $this->successResponse(
            ProductResource::collection($products),
            'Products retrieved successfully',
            200
        );
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->with([
                'category',
                'brand',
                'attributeValues.attribute',
                'mainImage',
                'galleryImages',
                'reviews'
            ])
            ->first();

        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }

        $selectedValueIds = [];
        $additionalPrice = 0;

        foreach (Request()->query() as $attributeNameOrSlug => $valueNameOrSlug) {

            $match = $product->attributeValues->first(function ($attributeValue) use ($attributeNameOrSlug, $valueNameOrSlug) {

                $attributeMatches = ($attributeValue->attribute->name) === strtolower($attributeNameOrSlug) ||
                    ($attributeValue->attribute->slug) === strtolower($attributeNameOrSlug);
                $valueMatches =
                    ($attributeValue->slug) === strtolower($valueNameOrSlug) ||
                    ($attributeValue->value) === strtolower($valueNameOrSlug);
                return $attributeMatches && $valueMatches;
            });

            if ($match) {
                $priceToAdd = $match->pivot->additional_price ?? 0;

                $selectedValueIds[] = $match->id;
                $additionalPrice += $priceToAdd;
            }
        }

        return $this->successResponse(
            [
                'product' => new ProductResource($product, $selectedValueIds, $additionalPrice),
            ],
            'Product retrieved successfully',
            200
        );
    }

    public function store(ProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $data = $request->validated();

            $product = Product::create($data);
            // dd($product);
            if ($request->has('attribute_values')) {


                foreach ($request->attribute_values as $attr) {
                    $product->attributeValues()->attach($attr['id'], [
                        'additional_price' => $attr['additional_price'] ?? 0,
                        'quantity' => $attr['quantity'],
                        'min_qty' => $attr['min_qty'] ?? 1,
                    ]);
                }
            }
            if ($request->hasFile('main_image')) {

                ImageHelper::uploadImage($product, $request->file('main_image'), 'products/main', 'main');
            }



            if ($request->hasFile('gallery_images')) {
                ImageHelper::uploadGallery($product, $request->file('gallery_images'), 'products/gallery');
            }
            $product->update([
                'total_quantity' => $product->attributeValues()->sum('product_attribute_values.quantity'),
            ]);
            DB::commit();



            return $this->successResponse(

                new ProductResource($product->load(['category', 'brand', 'attributeValues', 'mainImage', 'galleryImages'])),
                'Product created successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Failed to create product',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function update(ProductRequest $request, $slug)
    {
        try {
            DB::beginTransaction();

            $product = Product::where('slug', $slug)->first();
            if (!$product) {
                return $this->errorResponse('Product not found', 404);
            }

            $data = $request->validated();
            $product->update($data);

            // if ($request->has('attribute_values')) {
            //     $syncData = [];
            //     foreach ($request->attribute_values as $attr) {
            //         $syncData[$attr['id']] = ['additional_price' => $attr['additional_price'] ?? 0];
            //     }
            // $product->attributeValues()->sync($syncData);
            // }
            if ($request->has('attribute_values')) {

                foreach ($request->attribute_values as $item) {
                    // تأكد أن الـ value مربوطة أصلاً بالمنتج (اختياري)
                    $product->attributeValues()->syncWithoutDetaching([
                        $item['id'] => [
                            "additional_price" => $item['additional_price'] ?? 0,
                            "quantity" => $item['quantity'] ?? 0,
                            "min_qty" => $item['min_qty'] ?? 1,
                        ]
                    ]);
                }
            }
            $product->update([
                'total_quantity' => $product->attributeValues()->sum('product_attribute_values.quantity'),
            ]);
            if ($request->hasFile('main_image')) {
                ImageHelper::updateImage($product, $request->file('main_image'), 'products/main', 'main');
            }
            if ($request->has('delete_gallery_ids') || $request->hasFile('gallery_images')) {
                ImageHelper::updateGallery(
                    $product,
                    $request->file('gallery_images', []),
                    $request->input('delete_gallery_ids', [])
                );
            }


            DB::commit();

            return $this->successResponse(
                new ProductResource($product->load(['category', 'brand', 'attributeValues', 'mainImage', 'galleryImages'])),
                'Product updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                'Failed to update product',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
    public function destroy($slug)
    {
        $product = Product::where('slug', $slug)->first();
        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }

        ImageHelper::deleteAll($product);

        $product->delete();

        return $this->successResponse(
            null,
            'Product deleted successfully',
            200
        );
    }
}

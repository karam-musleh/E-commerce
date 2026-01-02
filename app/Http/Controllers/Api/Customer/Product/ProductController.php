<?php

namespace App\Http\Controllers\Api\Customer\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    use ApiResponserTrait;
    public function index(Request $request)
    {
        $orderBy = Request()->query('order_by', 'main_price');
        $sort = Request()->query('sort', 'asc');
        $perPage = Request()->query('per_page', 5);
        $products = Product::select('id', 'name', 'slug', 'main_price', 'discount', 'discount_type')
            ->with([
                'category:id,name,slug',
                'brand:id,name,slug',
                'mainImage:id,imageable_id,imageable_type,path'
            ])
            ->active()
            ->when(Request()->query('min_price'), function ($query, $minPrice) {
                $query->where('main_price', '>=', round($minPrice * 100));
            })
            ->when(Request()->query('max_price'), function ($query, $maxPrice) {
                $query->where('main_price', '<=', round($maxPrice * 100));
            })
            ->when(Request()->query('q'), function ($query) {
                $search = Request()->query('q');
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
        return $this->successResponse(
            ProductResource::collection($products),
            'Products fetched successfully',
            200
        );
    }

    //
    // public function show(Product $product)
    // {
    // $product = Product::Active()
    //     ->where('slug', $product->slug)
    //     ->with([
    //         'category:id,name,slug',
    //         'brand:id,name,slug',
    //         'attributeValues.attribute:id,name,slug',
    //         'mainImage',
    //         'galleryImages',
    //         'approvedReviews.user:id,name',
    //     ])->first();
    //         dd($product);
    // $additionalPrice = 0;
    // $selectedValueIds = [];

    // foreach (request()->query() as $attributeNameOrSlug => $valueNameOrSlug) {
    //     $match = $product->attributeValues->first(function ($attrValue) use ($attributeNameOrSlug, $valueNameOrSlug) {
    //         $attributeMatches = strtolower($attrValue->attribute->name) === strtolower($attributeNameOrSlug) ||
    //             strtolower($attrValue->attribute->slug) === strtolower($attributeNameOrSlug);
    //         $valueMatches = strtolower($attrValue->slug) === strtolower($valueNameOrSlug) ||
    //             strtolower($attrValue->value) === strtolower($valueNameOrSlug);
    //         return $attributeMatches && $valueMatches;
    //     });

    //     if ($match) {
    //         $selectedValueIds[] = $match->id;
    //         $additionalPrice += $match->pivot->additional_price ?? 0;
    //     }
    // }
    // dd($product);

    // return $this->successResponse(
    //     new ProductResource($product, $selectedValueIds, $additionalPrice),
    //     'Product retrieved successfully',
    //     200
    // );

    // ✅ تحقق من حالة المنتج
    //
    public function show($slug)
    {
        // // ✅ تأكد أن الـ product موجود
        // if (!$product) {
        //     return $this->errorResponse('Product not found', 404);
        // }

        // // تحقق من الحالة
        // if ($product->status !== Product::STATUS_ACTIVE) {
        //     return $this->errorResponse('Product not available', 404);
        // }

        // // تحميل العلاقات
        // $product->load([
        //     'category',
        //     'brand',
        //     'attributeValues.attribute',
        //     'mainImage',
        //     'galleryImages',
        //     'approvedReviews.user',
        // ]);
        $product = Product::Active()
            ->where('slug', $slug)
            ->with([
                'category:id,name,slug',
                'brand:id,name,slug',
                'attributeValues.attribute:id,name,slug',
                'mainImage',
                'galleryImages',
                'approvedReviews.user:id,name',
            ])->first();
        // dd([
        //     'from_db' => $product->getAttributes(),  // البيانات من الـ DB مباشرة
        //     'total_quantity_raw' => $product->getRawOriginal('total_quantity'),
        //     'total_quantity_accessor' => $product->total_quantity,
        // ]);
        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }
        // dd($product->total_quantity);

        //   dd([
        //         'product_exists' => !is_null($product),
        //         'product_id' => $product?->id,
        //         'product_name' => $product?->name,
        //         'product_slug' => $product?->slug,
        //     ]);
        // حساب السعر الإضافي
        $additionalPrice = 0;
        $selectedValueIds = [];

        foreach (request()->query() as $attributeNameOrSlug => $valueNameOrSlug) {
            $match = $product->attributeValues->first(function ($attrValue) use ($attributeNameOrSlug, $valueNameOrSlug) {
                $attributeMatches =
                    strtolower($attrValue->attribute->name) === strtolower($attributeNameOrSlug) ||
                    strtolower($attrValue->attribute->slug) === strtolower($attributeNameOrSlug);

                $valueMatches =
                    strtolower($attrValue->slug) === strtolower($valueNameOrSlug) ||
                    strtolower($attrValue->value) === strtolower($valueNameOrSlug);

                return $attributeMatches && $valueMatches;
            });

            if ($match) {
                $selectedValueIds[] = $match->id;
                $additionalPrice += $match->pivot->additional_price ?? 0;
            }
        }

        // ✅ تأكد من تمرير البيانات صح
        return $this->successResponse(
            new ProductResource($product, $selectedValueIds, $additionalPrice),
            'Product retrieved successfully'
        );
    }
}

<?php

namespace App\Http\Controllers\Api\Customer\Product;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ReviewResource;
use App\Models\User;

class ReviewController extends Controller
{
    use ApiResponserTrait;

    // index
    public function index(Product $product)
    {
        $per_page = request()->get('per_page', 5);
        // $product = Product::where('slug', $slug)->first();
        // if (!$product) {
        //     return $this->errorResponse('Product not found', 404);
        // }
        // dd($product);
        $reviews = $product
            ->approvedReviews()
            ->with('user')
            ->latest()
            ->paginate($per_page);
        dd($reviews);
        return $this->successResponse(
            ReviewResource::collection($reviews),
            'Reviews fetched successfully',
            200
        );
    }

    public function store(ReviewRequest $request,  Product $product)
    {
        // $product = Product::where('slug', $slug)->first();
        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }
        $user = Auth::guard('api')->user();

        if (! $user instanceof \App\Models\User) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        if ($user->hasReviewedProduct($product->id)) {
            return
                $this->errorResponse('You have already reviewed this product', 400);
        }

        $data = $request->validated();
        $review = $product->reviews()->create([
            'user_id' => $user->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'status' => Review::STATUS_PENDING
        ]);
        $review->load('user');

        return $this->successResponse(
            new ReviewResource($review),
            'Review submitted successfully and is pending approval',
            201
        );
    }
    public function update(ReviewRequest $request, Product $product, Review $review)
    {
        // $user = Auth::guard('api')->user();
        if ($review->user_id !== Auth::guard('api')->id()) {
            return $this->errorResponse('You are not authorized to update this review', 403);
        }

        if ($product->id !== $review->product_id) {
            return $this->errorResponse('This review does not belong to the specified product', 400);
        }
        if ($review->isApproved()) {
            return $this->errorResponse('Approved reviews cannot be updated', 400);
        }
        $review->update($request->validated());

        return $this->successResponse(
            new ReviewResource($review),
            'Review updated successfully and is pending approval',
            200
        );
    }

    public function destroy(Product $product, Review $review)
    {
        if ($review->user_id !== Auth::guard('api')->id()) {
            return $this->errorResponse('You are not authorized to delete this review', 403);
        }


        if ($product->id !== $review->product_id) {
            return $this->errorResponse('This review does not belong to the specified product', 400);
        }
        // $review = Review::where('id', $review->id)
        //     ->where('user_id', Auth::guard('api')->id())
        //     ->firstOrFail();

        $review->delete();

        return $this->successResponse(
            null,
            'Review deleted successfully',
            200
        );
    }

    public function myReviews()
    {
        /** @var User $user */
        $per_page = request()->get('per_page', 5);
        $user = Auth::guard('api')->user();

        if (! $user instanceof \App\Models\User) {
            return $this->errorResponse('Unauthenticated', 401);
        }
        // dd( $user);
        $reviews = $user
            ->reviews()
            ->with('product')
            ->latest()
            ->paginate($per_page);
        return $this->successResponse(
            ReviewResource::collection($reviews),
            'My Reviews fetched successfully',
            200
        );
    }



    // $product = Product::where('slug', $slug)->first();
    // if (!$product) {
    //     return $this->errorResponse('Product not found', 404);
    // }
    // $user = Auth::guard('api')->user();


    // $review = $product->reviews()->where('id', $id)->where('user_id', $user->id)->first();
    // if (!$review) {
    //     return $this->errorResponse('Review not found', 404);
    // }
    // $review->update([
    //     'rating' => $request->rating,
    //     'comment' => $request->comment,
    //     'status' => Review::STATUS_PENDING
    // ]);
    // $review->load('user');

    // return $this->successResponse(
    //     new ReviewResource($review),
    //     'Review updated successfully and is pending approval',
    //     200
    // );

}

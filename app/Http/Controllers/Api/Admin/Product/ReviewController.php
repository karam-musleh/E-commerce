<?php

namespace App\Http\Controllers\Api\admin\Product;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;

class ReviewController extends Controller
{
    use ApiResponserTrait;

    /**
     * Fetch reviews with optional filters: status, rating, product_id
     */
    public function index()
    {

        $perPage = Request()->query('per_page', 20);

        $reviews = Review::with(['user:id,name', 'product:id,name'])
            ->when(Request()->query('status'), fn($q, $status) => $q->where('status', $status))
            ->when(Request()->query('rating'), fn($q, $rating) => $q->where('rating', $rating))
            ->when(Request()->query('product_id'), fn($q, $productId) => $q->where('product_id', $productId))
            ->latest()
            ->paginate($perPage);

        return $this->successResponse(
            ReviewResource::collection($reviews),
            'Reviews fetched successfully',
            200
        );
    }

    public function approve(Review $review)
    {
        if ($review->isApproved()) {
            return $this->errorResponse('Review is already approved', 400);
        }

        $review->updateStatus(Review::STATUS_APPROVED);

        return $this->successResponse(
            new ReviewResource($review->load(['user:id,name', 'product:id,name'])),
            'Review approved successfully'
        );
    }


    public function reject(Review $review)
    {
        if ($review->isRejected()) {
            return $this->errorResponse('Review is already rejected', 400);
        }

        $review->updateStatus(Review::STATUS_REJECTED);

        return $this->successResponse(
            new ReviewResource($review->load(['user:id,name', 'product:id,name'])),
            'Review rejected successfully'
        );
    }


    public function destroy(Review $review)
    {
        $review->delete();

        return $this->successResponse(
            null,
            'Review deleted successfully'
        );
    }
}

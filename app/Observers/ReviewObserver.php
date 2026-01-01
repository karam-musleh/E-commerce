<?php

namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        //
        $review->product->update([
            'reviews_count' => $review->product->reviews()->count(),
            'rating_avg' => $review->product->reviews()->avg('rating'),
        ]);
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review)
    {
        $review->product->update([
            'rating_avg' => $review->product->reviews()->avg('rating'),
        ]);
    }
    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review)
    {
        $review->product->update([
            'reviews_count' => $review->product->reviews()->count(),
            'rating_avg' => $review->product->reviews()->avg('rating') ?? 0,
        ]);
    }
}

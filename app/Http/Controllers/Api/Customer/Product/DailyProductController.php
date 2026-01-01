<?php

namespace App\Http\Controllers\Api\Customer\Product;

use App\Models\DailyDeal;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\DailyDealResource;

class DailyProductController extends Controller
{
    //
    use ApiResponserTrait;
    public function index()
    {
        $orderBy = request()->query('order_by', 'starts_at');
        $sort = request()->query('sort', 'asc');
        $perPage = request()->query('per_page', 10);

        $dailyDeals = DailyDeal::with('product:id,name,slug,main_price')
            ->active()
            ->orderBy($orderBy, $sort)
            ->paginate($perPage);

        if ($dailyDeals->isEmpty()) {
            return $this->errorResponse("No daily deals found", 404);
        }
        return $this->successResponse(
            DailyDealResource::collection($dailyDeals),
            'Daily deals retrieved successfully'
        );
    }
    public function show($id)
    {
        $dailyDeal = DailyDeal::with('product:id,name,slug,main_price')
            ->active()
            ->find($id);
        if (!$dailyDeal) {
            return $this->errorResponse("Daily deal not found", 404);
        }
        $dailyDeal->load('product');
        return $this->successResponse(
            new DailyDealResource($dailyDeal),
            'Daily deal retrieved successfully'
        );
    }
}

<?php

namespace App\Http\Controllers\Api\Admin\Product;

use App\Models\Product;
use App\Models\DailyDeal;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\DailyDealResource;
use App\Http\Requests\DailyProductRequest;

class DailyProductController extends Controller
{
    use ApiResponserTrait;
    //  index method
    public function index()
    {
        $orderBy = request()->query('order_by', 'starts_at');
        $sort = request()->query('sort', 'asc');
        $perPage = request()->query('per_page', 10);

        $dailyDeals = DailyDeal::with('product:id,name,slug,main_price')
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
    // show
    public function show($id)
    {
        $dailyDeal = DailyDeal::with('product:id,name,slug,main_price')->find($id);
        if (!$dailyDeal) {
            return $this->errorResponse("Daily deal not found", 404);
        }
        $dailyDeal->load('product');
        return $this->successResponse(
            new DailyDealResource($dailyDeal),
            'Daily deal retrieved successfully'
        );
    }
    // store method
    public function store(DailyProductRequest $request)
    {
        $data = $request->validated();
        $dailyDeal = DailyDeal::create($data);
        $dailyDeal->load('product');
        $dailyDeal->refresh();


        return $this->successResponse(
            new DailyDealResource($dailyDeal),
            'Daily deal created successfully',
            201
        );
    }


    // update method
    public function update(DailyProductRequest $request, $id)
    {
        $dailyDeal = DailyDeal::find($id);
        if (!$dailyDeal) {
            return $this->errorResponse("Daily deal not found", 404);
        }

        $data = $request->validated();
        if (
            isset($data['status'])
            && $data['status'] === DailyDeal::STATUS_ACTIVE
            && $dailyDeal->is_expired
        ) {

            return $this->errorResponse("Cannot active an expired deal", 400);
        }

        $dailyDeal->update($data);

        return $this->successResponse(
            new DailyDealResource($dailyDeal),
            'Daily deal updated successfully'
        );
    }
    // destroy method
    public function destroy($id)
    {
        $dailyDeal = DailyDeal::find($id);
        if (!$dailyDeal) {
            return $this->errorResponse("Daily deal not found", 404);
        }
        $dailyDeal->delete();
        return $this->successResponse(
            null,
            'Daily deal deleted successfully',
            200
        );
    }
}

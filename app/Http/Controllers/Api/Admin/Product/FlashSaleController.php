<?php

namespace App\Http\Controllers\Api\admin\Product;

use App\Models\FlashSale;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\FlashSaleRequest;
use App\Http\Resources\FlashSaleResource;

class FlashSaleController extends Controller
{
    //
    use ApiResponserTrait;

    public function index()
    {
        $orderBy = request()->query('order_by', 'starts_at');
        $sort = request()->query('sort', 'asc');
        $perPage = request()->query('per_page', 10);

        $flashSales = FlashSale::with('product:id,name,slug,main_price')
            ->orderBy($orderBy, $sort)
            ->paginate($perPage);

        if ($flashSales->isEmpty()) {
            return $this->errorResponse('No flash sales found', 404);
        }

        return $this->successResponse(
            FlashSaleResource::collection($flashSales),
            'Flash sales retrieved successfully',
            200
        );
    }
    public function show($id)
    {
        $flashSale = FlashSale::with('product:id,name,slug,main_price')->find($id);

        if (!$flashSale) {
            return $this->errorResponse('Flash sale not found', 404);
        }

        return $this->successResponse(
            new FlashSaleResource($flashSale),
            'Flash sale retrieved successfully',
            200
        );
    }
    public function store(FlashSaleRequest $request)
    {
        $data = $request->validated();
        $flashSale = FlashSale::create($data);
        $flashSale->load('product');

        return $this->successResponse(
            new FlashSaleResource($flashSale),
            'Flash sale created successfully',
            201
        );
    }
    public function update(FlashSaleRequest $request, $id)
    {
        $flashSale = FlashSale::find($id);

        if (!$flashSale) {
            return $this->errorResponse('Flash sale not found', 404);
        }

        $data = $request->validated();

        if (
            isset($data['status']) &&
            $data['status'] === FlashSale::STATUS_ACTIVE &&
            $flashSale->is_expired
        ) {
            return $this->errorResponse(
                'Cannot activate an expired flash sale',
                400
            );
        }

        $flashSale->update($data);

        return $this->successResponse(
            new FlashSaleResource($flashSale),
            'Flash sale updated successfully'
        );
    }
    public function destroy($id)
    {
        $flashSale = FlashSale::find($id);

        if (!$flashSale) {
            return $this->errorResponse('Flash sale not found', 404);
        }

        $flashSale->delete();

        return $this->successResponse(null, 'Flash sale deleted successfully', 200);
    }
}

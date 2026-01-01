<?php

namespace App\Http\Controllers\Api\Customer\Product;

use App\Models\FlashSale;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
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
            ->active()
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
        $flashSale = FlashSale::with('product:id,name,slug,main_price')
            ->active()
            ->find($id);

        if (!$flashSale) {
            return $this->errorResponse('Flash sale not found', 404);
        }

        return $this->successResponse(
            new FlashSaleResource($flashSale),
            'Flash sale retrieved successfully',
            200
        );
    }
}

<?php

namespace App\Http\Controllers\Api\Customer\Order;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\Order\OrderResource;

class OrderController extends Controller
{
use ApiResponserTrait;

protected OrderService $orderService;


    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(CreateOrderRequest $request)
    {
        $data = $request->validated();
        $order = $this->orderService->createOrderFromCart($data);
        return $this->successResponse(
            new OrderResource($order),
            'Order created successfully',
        );
    }
    public function index(){
        $per_page = request()->get('per_page', 10);
        $orders = Order::where('user_id', auth('api')->id())
        ->latest()
        ->with(['items.product','address'])
        ->paginate($per_page);
        return $this->successResponse(
            OrderResource::collection($orders),
            'Orders retrieved successfully',
        );
    }
            public function show(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', auth('api')->id())
            ->with([
                'items.product',
                'items.itemAttributes',
                'address'
            ])
            ->firstOrFail();

        return $this->successResponse(
            new OrderResource($order),
            'Order fetched successfully'
        );
    }
        public function cancel( string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', auth('api')->id())
            ->firstOrFail();

        try {
            $order = $this->orderService->cancelOrder(
                $order,
                Request()->input('reason')
            );

            return $this->successResponse(
                new OrderResource($order),
                'Order cancelled successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    //  public function cancel(Request $request, string $orderNumber)
    // {
    //     $order = Order::where('order_number', $orderNumber)
    //         ->where('user_id', auth('api')->id())
    //         ->firstOrFail();

    //     try {
    //         $order = $this->orderService->cancelOrder(
    //             $order,
    //             $request->input('reason')
    //         );

    //         return $this->successResponse(
    //             new OrderResource($order),
    //             'Order cancelled successfully'
    //         );
    //     } catch (\Exception $e) {
    //         return $this->errorResponse($e->getMessage(), 400);
    //     }
    // }



    }





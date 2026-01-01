<?php

namespace App\Http\Controllers\Api\Admin\Order;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Http\Resources\Order\OrderResource;

class OrderController extends Controller
{
    use ApiResponserTrait;

    protected OrderService $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    public function index()
    {
        $q = Request();
        $per_page = $q->get('per_page', 20);
        $query = Order::with(['user', 'items.product', 'address']);

        // ✅ فلتر حسب الحالة
        if ($q->has('status')) {
            $query->where('status', $q->status);
        }

        // ✅ فلتر حسب حالة الدفع
        if ($q->has('payment_status')) {
            $query->where('payment_status', $q->payment_status);
        }

        // ✅ فلتر حسب المستخدم
        if ($q->has('user_id')) {
            $query->where('user_id', $q->user_id);
        }

        // ✅ بحث برقم الطلب
        if ($q->has('order_number')) {
            $query->where('order_number', 'LIKE', "%{$q->order_number}%");
        }

        // ✅ ترتيب
        $orders = $query->latest()->paginate($per_page);

        return OrderResource::collection($orders);
    }

    public function show(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with([
                'user',
                'items.product',
                'items.attributes',
                'address'
            ])
            ->firstOrFail();

        return $this->successResponse(
            new OrderResource($order),
            'Order fetched successfully'
        );
    }
    // status  order update
    public function updateState()
    {
        $q = Request();
        $q->validate([
            'status' => 'required|string|in:pending,confirmed,shipped,delivered,cancelled',
        ]);
        $order = Order::where('order_number', $q->order_number)->firstOrFail();
        $order = $this->orderService->updateOrderStatus(
            $order,
            $q->status
        );
        return $this->successResponse(
            new OrderResource($order),
            'Order status updated successfully'
        );
    }
    // payment status update
    public function updatePaymentStatus()
    {
        $q = Request();
        $q->validate([
            'payment_status' => 'required|string|in:unpaid,paid,failed,refunded',
        ]);
        $order = Order::where('order_number', $q->order_number)->firstOrFail();
        $order = $this->orderService->updatePaymentStatus(
            $order,
            $q->payment_status
        );
        return $this->successResponse(
            new OrderResource($order),
            'Order payment status updated successfully'
        );
    }
    public function updateDeliveryStatus()
    {
        $q = Request();
        $q->validate([
            'delivery_status' => 'required|string|in:pending,processing,shipped,delivered',
        ]);
        $order = Order::where('order_number', $q->order_number)->firstOrFail();
        $order = $this->orderService->updateDeliveryStatus(
            $order,
            $q->delivery_status
        );
        return $this->successResponse(
            new OrderResource($order),
            'Order delivery status updated successfully'
        );
    }
    public function destroy(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $order->delete();

        return $this->successResponse(null, 'Order deleted successfully');
    }
}

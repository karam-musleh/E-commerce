<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\PaymentService;
use App\Services\Payment\PaymentGatewayResolver;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function pay(
        Request $request,
        Order $order,
        PaymentService $paymentService,
        PaymentGatewayResolver $resolver
    ) {
        if ($order->payment_status === 'paid') {
            abort(400, 'Order already paid');
        }

        $payment = $paymentService->initiate(
            $order,
            $request->payment_method,
            $request->payment_gateway
        );

        $gateway = $resolver->resolve($request->payment_gateway);

        $gatewayData = $gateway->createPayment($order, $payment);

        return response()->json([
            'payment_reference' => $payment->payment_reference,
            'gateway_data' => $gatewayData,
        ]);
    }
}

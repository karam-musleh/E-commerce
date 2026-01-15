<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Contracts\PaymentGatewayInterface;

class PaymentService
{
    public function initiate(Order $order, string $method, string $gateway): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'amount' => $order->total_amount,
            'payment_method' => $method,
            'payment_gateway' => $gateway,
            'status' => Payment::STATUS_PENDING,
        ]);
    }

    public function markAsPaid(Payment $payment, array $payload = []): void
    {
        $payment->update([
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
            'gateway_response' => $payload,
        ]);

        $payment->order->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
    }

    public function markAsFailed(Payment $payment, string $reason = null, array $payload = []): void
    {
        $payment->update([
            'status' => Payment::STATUS_FAILED,
            'failed_at' => now(),
            'failure_reason' => $reason,
            'gateway_response' => $payload,
        ]);
    }
}

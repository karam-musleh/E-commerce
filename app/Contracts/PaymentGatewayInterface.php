<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    public function createPayment(Order $order, Payment $payment): array;

    public function handleWebhook(array $payload): void;

    public function verify(array $payload): bool;
}

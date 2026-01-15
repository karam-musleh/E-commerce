<?php

namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Payment;

class FakeGateway implements PaymentGatewayInterface
{
    public function createPayment(Order $order, Payment $payment): array
    {
        $payment->update([
            'transaction_id' => 'fake_tx_' . $payment->id,
        ]);

        return [
            'redirect_url' => route('fake.pay', $payment),
        ];
    }

    public function handleWebhook(array $payload): void
    {
        //
    }

    public function verify(array $payload): bool
    {
        return true;
    }
}

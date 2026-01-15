<?php

namespace App\Services\Payment;

use InvalidArgumentException;
use App\Gateways\PaypalGateway;
use App\Gateways\StripeGateway;
use Illuminate\Support\Facades\App;
use App\Contracts\PaymentGatewayInterface;

class PaymentGatewayResolver
{
    public function resolve(string $gateway): PaymentGatewayInterface

    {
        $class = config("payments.gateways.$gateway.class");

        if (! $class || ! class_exists($class)) {
            throw new InvalidArgumentException('Unsupported payment gateway');
        }

        return App::make($class);
    }
}

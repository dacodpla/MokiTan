<?php

namespace App\Services;

use App\Models\Order;

interface PaymentGateway
{
    /**
     * @param  array{number: string, exp: string, cvc: string}  $card
     */
    public function charge(Order $order, array $card): PaymentResult;
}

<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGateway
{
    /**
     * @param  array{number: string, exp: string, cvc: string}  $card
     */
    public function charge(Order $order, array $card): PaymentResult
    {
        usleep(600_000);

        $normalized = preg_replace('/\D+/', '', $card['number']);

        if (str_ends_with((string) $normalized, '0000')) {
            return new PaymentResult(
                succeeded: false,
                message: 'Card declined by issuer. Please try another card.',
            );
        }

        return new PaymentResult(
            succeeded: true,
            reference: 'CH-'.strtoupper(Str::random(10)),
        );
    }
}

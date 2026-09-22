<?php

namespace App\Services;

class PaymentResult
{
    public function __construct(
        public bool $succeeded,
        public ?string $message = null,
        public ?string $reference = null,
    ) {}
}

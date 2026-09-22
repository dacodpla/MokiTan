<?php

namespace App\Support;

class Money
{
    public static function idr(int|float|null $amount): string
    {
        return 'Rp '.number_format((int) ($amount ?? 0), 0, ',', '.');
    }
}

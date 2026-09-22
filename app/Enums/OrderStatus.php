<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Shipped = 'shipped';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting Payment',
            self::Paid => 'Paid',
            self::Failed => 'Payment Failed',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
        };
    }
}

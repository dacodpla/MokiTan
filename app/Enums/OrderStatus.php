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

    /**
     * Tailwind classes for the status badge. Stays within the DESIGN.md
     * palette (brand/ink/surface/canvas) by varying fill vs. outline instead
     * of introducing new hues.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-surface text-ink',
            self::Paid => 'bg-brand text-canvas',
            self::Failed => 'border border-brand text-brand',
            self::Shipped => 'border border-ink text-ink',
            self::Delivered => 'bg-ink text-canvas',
        };
    }
}

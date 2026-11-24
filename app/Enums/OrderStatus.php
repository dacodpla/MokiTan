<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Shipped => 'Shipped',
        };
    }

    /**
     * Status badge colours required by the UPRAK brief:
     * Pending = yellow, Paid = blue, Shipped = green.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-status-pending-bg text-status-pending-fg',
            self::Paid => 'bg-status-paid-bg text-status-paid-fg',
            self::Shipped => 'bg-status-shipped-bg text-status-shipped-fg',
        };
    }

    /**
     * Statuses that count towards net revenue (omzet bersih).
     *
     * @return array<int, string>
     */
    public static function revenueValues(): array
    {
        return [self::Paid->value, self::Shipped->value];
    }
}

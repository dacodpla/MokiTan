<?php

namespace App\Enums;

enum Courier: string
{
    case JneReguler = 'JNE Reguler';
    case JntExpress = 'J&T Express';
    case SicepatBest = 'SiCepat BEST';

    /**
     * Flat shipping fee in whole rupiah.
     */
    public function fee(): int
    {
        return match ($this) {
            self::JneReguler => 20000,
            self::JntExpress => 18000,
            self::SicepatBest => 15000,
        };
    }
}

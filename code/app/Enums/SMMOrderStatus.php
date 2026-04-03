<?php

namespace App\Enums;

enum SMMOrderStatus: string
{
    case PENDING    = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED  = 'completed';
    case FAILED     = 'failed';
    case REFUNDED   = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING    => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED  => 'Completed',
            self::FAILED     => 'Failed',
            self::REFUNDED   => 'Refunded',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::PENDING    => 'badge--warning',
            self::PROCESSING => 'badge--primary',
            self::COMPLETED  => 'badge--success',
            self::FAILED     => 'badge--danger',
            self::REFUNDED   => 'badge--secondary',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    public static function labels(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($c) => $c->label(), self::cases())
        );
    }
}

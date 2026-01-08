<?php

namespace App\Enums;

enum TextDirection: string
{
    case LTR = 'ltr';
    case RTL = 'rtl';


    public static function toArray(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}

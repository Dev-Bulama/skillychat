<?php

namespace App\Enums;

enum AiModuleType :string {

    use EnumTrait;

    case TEXT    = 'text';
    case IMAGE   = 'image';
    case VIDEO   = 'video';

    /**
     * @return array
     */
    public static function toArray(): array
    {
        return array_combine(self::names(), self::values(),);
    }


}

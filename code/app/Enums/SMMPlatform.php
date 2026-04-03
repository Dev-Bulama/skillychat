<?php

namespace App\Enums;

enum SMMPlatform: string
{
    case INSTAGRAM = 'instagram';
    case TIKTOK    = 'tiktok';
    case YOUTUBE   = 'youtube';
    case FACEBOOK  = 'facebook';
    case TWITTER   = 'twitter';
    case TELEGRAM  = 'telegram';
    case SPOTIFY   = 'spotify';
    case LINKEDIN  = 'linkedin';
    case THREADS   = 'threads';
    case OTHER     = 'other';

    public function label(): string
    {
        return match($this) {
            self::INSTAGRAM => 'Instagram',
            self::TIKTOK    => 'TikTok',
            self::YOUTUBE   => 'YouTube',
            self::FACEBOOK  => 'Facebook',
            self::TWITTER   => 'Twitter / X',
            self::TELEGRAM  => 'Telegram',
            self::SPOTIFY   => 'Spotify',
            self::LINKEDIN  => 'LinkedIn',
            self::THREADS   => 'Threads',
            self::OTHER     => 'Other',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::INSTAGRAM => 'bi-instagram',
            self::TIKTOK    => 'bi-tiktok',
            self::YOUTUBE   => 'bi-youtube',
            self::FACEBOOK  => 'bi-facebook',
            self::TWITTER   => 'bi-twitter-x',
            self::TELEGRAM  => 'bi-telegram',
            self::SPOTIFY   => 'bi-spotify',
            self::LINKEDIN  => 'bi-linkedin',
            self::THREADS   => 'bi-threads',
            self::OTHER     => 'bi-globe',
        };
    }

    public static function toArray(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->label();
        }
        return $result;
    }
}

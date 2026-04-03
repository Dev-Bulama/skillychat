<?php

namespace App\Enums;

enum SMMServiceType: string
{
    case FOLLOWERS   = 'followers';
    case LIKES       = 'likes';
    case VIEWS       = 'views';
    case SUBSCRIBERS = 'subscribers';
    case SHARES      = 'shares';
    case COMMENTS    = 'comments';
    case SAVES       = 'saves';
    case STORY_VIEWS = 'story_views';
    case LIVE_VIEWS  = 'live_views';
    case IMPRESSIONS = 'impressions';
    case PLAYS       = 'plays';
    case WATCH_TIME  = 'watch_time';
    case OTHER       = 'other';

    public function label(): string
    {
        return match($this) {
            self::FOLLOWERS   => 'Followers',
            self::LIKES       => 'Likes',
            self::VIEWS       => 'Views',
            self::SUBSCRIBERS => 'Subscribers',
            self::SHARES      => 'Shares',
            self::COMMENTS    => 'Comments',
            self::SAVES       => 'Saves',
            self::STORY_VIEWS => 'Story Views',
            self::LIVE_VIEWS  => 'Live Views',
            self::IMPRESSIONS => 'Impressions',
            self::PLAYS       => 'Plays / Streams',
            self::WATCH_TIME  => 'Watch Time',
            self::OTHER       => 'Other',
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

<?php

// App/Traits/FeedNormalizer.php
namespace App\Traits;

use Illuminate\Support\Arr;

trait FeedNormalizer
{
    public function normalizeFeed(array $rawResponse, string $platformSlug): array
    {
        $posts = [];

        foreach (Arr::get($rawResponse, 'data', []) as $item) {
            $post = [];

            // Caption
            $post['caption'] = Arr::get($item, 'text')
                ?? Arr::get($item, 'message')
                ?? Arr::get($item, 'caption')
                ?? Arr::get($item, 'commentary')
                ?? '';

            // Timestamp
            $post['timestamp'] = Arr::get($item, 'timestamp')
                ?? Arr::get($item, 'created_time')
                ?? Arr::get($item, 'created_at')
                ?? now()->toDateTimeString();

            // Media URL
            $mediaUrl = Arr::get($item, 'media_url') ?? Arr::get($item, 'full_picture') ?? '';

            if (!$mediaUrl) {
                $mediaUrl = Arr::get($item, 'attachments.data.0.media.image.src');
            }

            if (!$mediaUrl && Arr::has($rawResponse, 'includes.media')) {
                $key = Arr::get($item, 'attachments.media_keys.0');
                if ($key) {
                    foreach (Arr::get($rawResponse, 'includes.media', []) as $media) {
                        if (Arr::get($media, 'media_key') === $key) {
                            $mediaUrl = Arr::get($media, 'url') ?? Arr::get($media, 'preview_image_url') ?? '';
                            break;
                        }
                    }
                }
            }
            $post['media_url'] = $mediaUrl;

            // Permalink
            $post['permalink'] = Arr::get($item, 'permalink')
                ?? Arr::get($item, 'permalink_url')
                ?? null;

            // Metrics with safe defaults
            $post['reactions'] = 0;
            $post['comments']  = 0;
            $post['shares']    = 0;
            $post['views']     = 0;

            if ($platformSlug === 'facebook') {
                $post['reactions'] = Arr::get($item, 'reactions.summary.total_count', 0);
                $post['comments']  = Arr::get($item, 'comments.summary.total_count', 0);
                $post['shares']    = Arr::get($item, 'shares.count', 0);
            } elseif (in_array($platformSlug, ['twitter', 'x'])) {
                $pm = Arr::get($item, 'public_metrics', []);
                $post['reactions'] = Arr::get($pm, 'like_count', 0);
                $post['comments']  = Arr::get($pm, 'reply_count', 0);
                $post['shares']    = Arr::get($pm, 'retweet_count', 0) + Arr::get($pm, 'quote_count', 0);
            } elseif ($platformSlug === 'threads') {
                $post['reactions'] = Arr::get($item, 'like_count', 0);
                $post['comments']  = Arr::get($item, 'reply_count', 0);
                $post['shares']    = Arr::get($item, 'repost_count', 0) + Arr::get($item, 'quote_count', 0);
                $post['views']     = Arr::get($item, 'view_count', 0);
            }

            $posts[] = $post;
        }

        return [
            'status'       => true,
            'data'         => $posts,
            'page_insights'=> Arr::get($rawResponse, 'page_insights', []),
        ];
    }
}
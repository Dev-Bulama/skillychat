<?php

namespace App\Services\SMM;

use App\Models\SMMProvider;

class SMMProviderFactory
{
    /**
     * Resolve a provider driver for a given SMMProvider model.
     * All supported providers use the same standard SMM REST API,
     * so a single GenericSMMProvider handles all of them.
     */
    public static function make(SMMProvider $provider): SMMProviderInterface
    {
        return new GenericSMMProvider($provider);
    }
}

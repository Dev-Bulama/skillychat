<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FeatureFlag extends Model
{
    protected $fillable = ['name', 'label', 'description', 'is_enabled'];

    protected $casts = ['is_enabled' => 'boolean'];

    /**
     * Check if a feature flag is enabled (cached).
     */
    public static function enabled(string $name): bool
    {
        return Cache::remember("feature_flag_{$name}", 300, function () use ($name) {
            $flag = self::where('name', $name)->first();
            return $flag ? (bool) $flag->is_enabled : true;
        });
    }

    /**
     * Toggle a flag and clear its cache.
     */
    public function toggle(): bool
    {
        $this->is_enabled = !$this->is_enabled;
        $this->save();
        Cache::forget("feature_flag_{$this->name}");
        return $this->is_enabled;
    }

    /**
     * Set a flag on/off and clear cache.
     */
    public function setEnabled(bool $value): void
    {
        $this->is_enabled = $value;
        $this->save();
        Cache::forget("feature_flag_{$this->name}");
    }
}

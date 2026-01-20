<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;

class Theme extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'author_url',
        'preview_image',
        'screenshots',
        'config',
        'status',
        'is_default',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'screenshots' => 'array',
        'config' => 'array',
        'is_default' => 'boolean',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the active theme
     *
     * @return Theme|null
     */
    public static function getActive(): ?Theme
    {
        return self::where('status', 'active')->first();
    }

    /**
     * Get the default theme
     *
     * @return Theme|null
     */
    public static function getDefault(): ?Theme
    {
        return self::where('is_default', true)->first();
    }

    /**
     * Activate this theme
     *
     * @return bool
     */
    public function activate(): bool
    {
        // Deactivate all other themes
        self::where('id', '!=', $this->id)->update(['status' => 'inactive']);

        // Activate this theme
        $this->status = 'active';
        return $this->save();
    }

    /**
     * Get the theme directory path
     *
     * @return string
     */
    public function getDirectoryPath(): string
    {
        return resource_path("views/themes/{$this->slug}");
    }

    /**
     * Check if theme directory exists
     *
     * @return bool
     */
    public function directoryExists(): bool
    {
        return File::isDirectory($this->getDirectoryPath());
    }

    /**
     * Get theme config value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Set theme config value
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setConfig(string $key, $value): void
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->config = $config;
    }

    /**
     * Scope for active themes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive themes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for system themes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope for non-system themes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }
}

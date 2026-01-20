<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use ZipArchive;

class ThemeService
{
    public function getActiveTheme(): ?Theme
    {
        return Cache::remember('active_theme', 3600, function () {
            return Theme::getActive();
        });
    }

    public function getAllThemes()
    {
        return Theme::orderBy('sort_order')->orderBy('name')->get();
    }

    public function activateTheme(int $themeId): array
    {
        try {
            $theme = Theme::findOrFail($themeId);

            if (!$theme->directoryExists()) {
                return ['success' => false, 'message' => translate('Theme files not found')];
            }

            DB::beginTransaction();
            $theme->activate();
            Cache::forget('active_theme');
            \Artisan::call('view:clear');
            DB::commit();

            return ['success' => true, 'message' => translate('Theme activated successfully')];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Theme activation failed: ' . $e->getMessage());
            return ['success' => false, 'message' => translate('Failed to activate theme')];
        }
    }

    public function installTheme($file): array
    {
        $tempPath = storage_path('app/temp/themes/');

        try {
            if (!File::exists($tempPath)) {
                File::makeDirectory($tempPath, 0755, true);
            }

            $zipPath = $tempPath . Str::random(10) . '.zip';
            $file->move($tempPath, basename($zipPath));

            $zip = new ZipArchive;
            if ($zip->open($zipPath) !== true) {
                return ['success' => false, 'message' => translate('Invalid ZIP file')];
            }

            $extractPath = $tempPath . 'extract_' . Str::random(10) . '/';
            File::makeDirectory($extractPath, 0755, true);
            $zip->extractTo($extractPath);
            $zip->close();

            $configPath = $extractPath . 'theme.json';
            if (!File::exists($configPath)) {
                File::deleteDirectory($extractPath);
                File::delete($zipPath);
                return ['success' => false, 'message' => translate('Invalid theme: theme.json not found')];
            }

            $config = json_decode(File::get($configPath), true);
            if (!isset($config['name'], $config['slug'])) {
                File::deleteDirectory($extractPath);
                File::delete($zipPath);
                return ['success' => false, 'message' => translate('Invalid theme configuration')];
            }

            if (Theme::where('slug', $config['slug'])->exists()) {
                File::deleteDirectory($extractPath);
                File::delete($zipPath);
                return ['success' => false, 'message' => translate('Theme already installed')];
            }

            $themePath = resource_path('views/themes/' . $config['slug']);
            if (File::exists($themePath)) {
                File::deleteDirectory($themePath);
            }
            File::moveDirectory($extractPath, $themePath);

            $theme = Theme::create([
                'name' => $config['name'],
                'slug' => $config['slug'],
                'description' => $config['description'] ?? null,
                'version' => $config['version'] ?? '1.0.0',
                'author' => $config['author'] ?? null,
                'author_url' => $config['author_url'] ?? null,
                'preview_image' => $config['preview_image'] ?? null,
                'screenshots' => $config['screenshots'] ?? null,
                'config' => $config['config'] ?? [],
                'status' => 'inactive',
                'is_system' => false,
            ]);

            File::delete($zipPath);

            return ['success' => true, 'message' => translate('Theme installed successfully'), 'theme' => $theme];
        } catch (\Exception $e) {
            \Log::error('Theme installation failed: ' . $e->getMessage());
            if (File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }
            return ['success' => false, 'message' => translate('Installation failed: ') . $e->getMessage()];
        }
    }

    public function deleteTheme(int $themeId): array
    {
        try {
            $theme = Theme::findOrFail($themeId);

            if ($theme->status === 'active') {
                return ['success' => false, 'message' => translate('Cannot delete active theme')];
            }

            if ($theme->is_system) {
                return ['success' => false, 'message' => translate('Cannot delete system theme')];
            }

            if ($theme->directoryExists()) {
                File::deleteDirectory($theme->getDirectoryPath());
            }

            $theme->delete();

            return ['success' => true, 'message' => translate('Theme deleted successfully')];
        } catch (\Exception $e) {
            \Log::error('Theme deletion failed: ' . $e->getMessage());
            return ['success' => false, 'message' => translate('Failed to delete theme')];
        }
    }

    public function updateThemeConfig(int $themeId, array $config): array
    {
        try {
            $theme = Theme::findOrFail($themeId);
            $theme->config = array_merge($theme->config ?? [], $config);
            $theme->save();

            if ($theme->status === 'active') {
                Cache::forget('active_theme');
                \Artisan::call('view:clear');
            }

            return ['success' => true, 'message' => translate('Theme configuration updated')];
        } catch (\Exception $e) {
            \Log::error('Theme config update failed: ' . $e->getMessage());
            return ['success' => false, 'message' => translate('Failed to update configuration')];
        }
    }
}

<?php

namespace App\Http\Services\Core;

use App\Enums\StatusEnum;
use App\Models\Core\Setting;
use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DemoService
{
    /**
     * Summary of isRestrictedRoute
     * @param \Illuminate\Http\Request $request
     * @param mixed $feature
     * @return bool
     */
    public function isRestrictedRoute(Request $request, string $feature): bool
    {
        $currentRoute = $request->route()->getName();
        $config = config("demo.feature.$feature", []);
        $routes = Arr::get($config, 'routes', []);
        $restrictions = Arr::get($config, 'restrictions', []);

        if (in_array($currentRoute, $routes, true)) {
            if (empty($restrictions)) {
                return true;
            }

            $requestData = $request->all();
            foreach (array_keys($restrictions) as $key) {
                if (Arr::has($requestData, $key)) {
                    return false; 
                }
            }
            return true;
        }
        return false;
    }


    /**
     * getFeatureForRoute
     *
     * @param Request $request
     * 
     * @return string|null
     */
    public function getFeatureForRoute(Request $request): ?string
    {
        $currentRoute = $request->route()->getName();
        $features = config('demo.feature', []);
        
        $disabledFeatures = [];
        $enabledFeatures = [];
        
        foreach ($features as $feature => $config) {
            if (isset($config['routes']) && in_array($currentRoute, $config['routes'], true)) {
                if (Arr::get($config, 'enabled', false)) {
                    $enabledFeatures[$feature] = $config;
                } else {
                    $disabledFeatures[$feature] = $config;
                }
            }
        }
        
        foreach ($disabledFeatures as $feature => $config) {
            return $feature;
        }
        
        foreach ($enabledFeatures as $feature => $config) {
            return $feature;
        }
        
        return null;
    }

    /**
     * isFeatureEnabled
     *
     * @param string $feature
     * 
     * @return bool
     */
    public function isFeatureEnabled(string $feature): bool
    {
        return config("demo.feature.{$feature}.enabled", false);
    }

    /**
     * getRestrictedKeys
     *
     * @param string $feature
     * 
     * @return array
     */
    public function getRestrictedKeys(string $feature): array
    {
        $restrictions = config("demo.feature.{$feature}.restrictions", []);
        return collect($restrictions)
            ->flatMap(function ($value, $key) {
                return $this->flattenKeys($value);
            })->all();
    }

    /**
     * getMessageForFeature
     *
     * @param string $feature
     * 
     * @return string
     */
    public function getMessageForFeature(string $feature): string
    {
        return config("demo.feature.{$feature}.default", $this->getGlobalMessage());
    }

    /**
     * flattenKeys
     *
     * @param array $keys
     * 
     * @return array
     */
    public function flattenKeys(array $keys): array
    {
        return collect($keys)
            ->flatMap(function ($value, $key) {
                if (is_array($value)) return $this->flattenKeys($value);
                return [$value];
            })->all();
    }

    /**
     * filterRestrictedKeys
     *
     * @param array $data
     * @param array $restrictedKeys
     * 
     * @return array
     */
    public function filterRestrictedKeys(array $data, array $restrictedKeys): array
    {
        return collect($data)
            ->filter(function ($value, $key) use ($restrictedKeys) {
                return !in_array($key, $restrictedKeys, true);
            })->mapWithKeys(function ($value, $key) use ($restrictedKeys) {
                if (is_array($value)) {
                    return [$key => $this->filterRestrictedKeys($value, $restrictedKeys)];
                }
                return [$key => $value];
            })->all();
    }

    /**
     * hasRestrictedKeys
     *
     * @param array $data
     * @param array $restrictedKeys
     * 
     * @return bool
     */
    public function hasRestrictedKeys(array $data, array $restrictedKeys): bool
    {
        return collect($data)
            ->contains(function ($value, $key) use ($restrictedKeys) {
                if (in_array($key, $restrictedKeys, true)) return true;
                if (is_array($value)) return $this->hasRestrictedKeys($value, $restrictedKeys);
                return false;
            });
    }

    /**
     * getGlobalMessage
     *
     * @return string
     */
    public function getGlobalMessage(): string
    {
        return config('demo.messages.global');
    }

    /**
     * appendGlobalMessage
     *
     * @param mixed $feature
     * @param Request $request
     * @param string $status
     * 
     * @return mixed
     */
    public function appendGlobalMessage($feature, Request $request, string $status = 'error'): mixed
    {
        $response = null;
        $message = $this->getMessageForFeature($feature);
        
        if ($request->expectsJson()) {
            $content = Arr::set(
                array: $content,
                key: 'message',
                value: $message
            );
            $response = new JsonResponse($content);
        } else {
            $response = back()->with($status, $message);
        }

        return $response;
    }

    /**
     * resetDatabase
     *
     * @return void
     */
    public function resetDatabase(): void
    {
        if (!config('demo.enabled')) return;
        if (!$this->validateResetConfig()) return;

        $lockFile = storage_path('demo_reset.lock');
        $handle = $this->acquireFileLock($lockFile);

        if (!$handle) return;

        try {
            $now = Carbon::now();
            $lastReset = $this->getLastResetTime();
            $nextReset = $lastReset 
                ? $this->calculateNextResetTime($lastReset) 
                : $now;

            if ($now->greaterThanOrEqualTo($nextReset)) {
                $this->executeFileReset();
                $this->executeDatabaseReset($now);
            }
        } catch (\Throwable $throwable) {
        } finally {
            Setting::updateOrInsert(['key' => "app_version"], ['key' => "app_version", 'value' => Arr::get(config("installer.core"),'appVersion',null)]);
            $this->releaseFileLock($handle);
        }
    }

    /**
     * executeFileReset
     *
     * @return void
     */
    protected function executeFileReset(): void
    {
        $backupZipPath = resource_path('data/backup-file.zip');
        $baseAssetsPath = base_path('../assets');
        $targetDirectories = [
            $baseAssetsPath . '/files',
            $baseAssetsPath . '/images'
        ];
        
        if (!file_exists($backupZipPath)) return;
        
        try {
            foreach ($targetDirectories as $targetDirectory) {
                if (is_dir($targetDirectory)) {
                    $this->removeDirectory($targetDirectory);
                }
            }
            
            if (!is_dir($baseAssetsPath)) {
                mkdir($baseAssetsPath, 0755, true);
            }
            
            $zip = new \ZipArchive();
            $result = $zip->open($backupZipPath);
            
            if ($result === TRUE) {
                $zip->extractTo($baseAssetsPath);
                $zip->close();
                
                foreach ($targetDirectories as $targetDirectory) {
                    if (is_dir($targetDirectory)) {
                        chmod($targetDirectory, 0755);
                    }
                }
            } 
        } catch (\Throwable $throwable) { }
    }

    /**
     * removeDirectory
     *
     * @param string $directory
     * @return bool
     */
    protected function removeDirectory(string $directory): bool
    {
        if (!is_dir($directory)) {
            return false;
        }
        
        $files = array_diff(scandir($directory), ['.', '..']);
        
        foreach ($files as $file) {
            $filePath = $directory . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($filePath)) {
                $this->removeDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
        
        return rmdir($directory);
    }

    /**
     * validateResetConfig
     *
     * @return bool
     */
    protected function validateResetConfig(): bool
    {
        $unit = config('demo.database_reset_unit', 'hour');
        $duration = config('demo.database_reset_duration', 4);
        $validUnits = [
            'second', 
            'minute', 
            'hour', 
            'day', 
            'month', 
            'year'
        ];

        return in_array($unit, $validUnits, true) && $duration > 0;
    }

    /**
     * acquireFileLock
     *
     * @param string $lockFile
     * 
     * @return mixed
     */
    protected function acquireFileLock(string $lockFile): mixed
    {
        $handle = fopen($lockFile, 'a+');
        if (!$handle || !flock($handle, LOCK_EX | LOCK_NB)) {
            if ($handle) {
                fclose($handle);
            }
            return null;
        }
        return $handle;
    }

    /**
     * releaseFileLock
     *
     * @param mixed $handle
     * 
     * @return void
     */
    protected function releaseFileLock($handle): void
    {
        if ($handle) {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * getLastResetTime
     *
     * @return Carbon|null
     */
    protected function getLastResetTime(): ?Carbon
    {
        $resetFile = storage_path('demo_reset.json');
        if (Storage::exists('demo_reset.json')) {
            $data = json_decode(Storage::get('demo_reset.json'), true);
            if (isset($data['last_reset_at'])) {
                return Carbon::parse($data['last_reset_at']);
            }
        }
        return null;
    }

    /**
     * calculateNextResetTime
     *
     * @param Carbon $lastReset
     * 
     * @return Carbon
     */
    protected function calculateNextResetTime(Carbon $lastReset): Carbon
    {
        $unit = config('demo.database_reset_unit', 'hour');
        $duration = config('demo.database_reset_duration', 4);
        $nextReset = $lastReset->copy();

        match ($unit) {
            'second' => $nextReset->addSeconds($duration),
            'minute' => $nextReset->addMinutes($duration),
            'hour' => $nextReset->addHours($duration),
            'day' => $nextReset->addDays($duration),
            'month' => $nextReset->addMonths($duration),
            'year' => $nextReset->addYears($duration),
        };

        return $nextReset;
    }

    /**
     * executeDatabaseReset
     *
     * @param Carbon $now
     * 
     * @return void
     */
    protected function executeDatabaseReset(Carbon $now): void
    {
        $sqlFile = resource_path('database/database_demo.sql');
        if (file_exists($sqlFile)) {
            $tables = DB::select('SHOW TABLES');
            $database = DB::getDatabaseName();
            $tableKey = 'Tables_in_' . $database;

            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                DB::statement("DROP TABLE IF EXISTS `$tableName`");
            }
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            DB::unprepared(file_get_contents($sqlFile));
            Storage::put('demo_reset.json', json_encode(['last_reset_at' => $now->toDateTimeString()]));
        }
    }

    
}
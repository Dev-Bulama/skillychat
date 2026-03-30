<?php

namespace App\Http\Controllers;

use App\Traits\InstallerManager;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use ZipArchive;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class SystemUpdateController extends Controller
{
    use InstallerManager;

    public function init(): View
    {
        return view('admin.system_update', [
            'title' => translate('Update System'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MANUAL ZIP UPDATE  (the revamped, minimal implementation)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Accept any ZIP file (GitHub archive, plain project ZIP, custom patch ZIP),
     * find the Laravel root inside it, copy files while protecting .env and
     * user uploads, run migrations, then clear caches.
     *
     * No config.json required.
     */
    public function update(Request $request): JsonResponse
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'updateFile' => ['required', 'file', 'mimes:zip', 'max:1048576'], // 1 GB
        ]);

        $tempDir = storage_path('app/update_' . uniqid() . '/');

        try {
            // 1. Save uploaded ZIP
            File::makeDirectory($tempDir, 0755, true);
            $request->file('updateFile')->move($tempDir, 'upload.zip');

            // 2. Extract
            $zip = new ZipArchive;
            if ($zip->open($tempDir . 'upload.zip') !== true) {
                throw new \RuntimeException('Invalid or corrupted ZIP file.');
            }
            $extractDir = $tempDir . 'src/';
            File::makeDirectory($extractDir, 0755, true);
            $zip->extractTo($extractDir);
            $zip->close();

            // 3. Find Laravel root (handles GitHub wrapper & nested structure)
            $sourceRoot = $this->findLaravelRoot($extractDir);
            if (!$sourceRoot) {
                throw new \RuntimeException(
                    'Could not find Laravel app root in ZIP. ' .
                    'Make sure the ZIP contains the artisan file.'
                );
            }

            // 4. Copy files, skipping protected paths
            $copyResult = $this->applyFiles($sourceRoot, base_path());

            // 5. Run new migrations
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                \Log::warning('Update: migrate warning — ' . $e->getMessage());
            }

            // 6. Clear caches
            try {
                Artisan::call('optimize:clear');
            } catch (\Throwable $e) {
                // non-fatal
            }

            // 7. Bump version if config.json is present
            $configPath = $sourceRoot . 'config.json';
            if (File::exists($configPath)) {
                $cfg = json_decode(File::get($configPath), true);
                if (!empty($cfg['version'])) {
                    DB::table('settings')->upsert(
                        [['key' => 'app_version', 'value' => $cfg['version']]],
                        ['key'],
                        ['value']
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => translate('System updated successfully! Reloading...'),
                'debug'   => [
                    'source_root'  => $sourceRoot,
                    'dest_root'    => base_path(),
                    'public_path'  => public_path(),
                    'files_copied' => $copyResult['copied'],
                    'files_skipped'=> $copyResult['skipped'],
                    'public_mapped'=> $copyResult['public_mapped'] ?? false,
                ],
            ]);
        } catch (\Throwable $e) {
            \Log::error('SystemUpdate::update — ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        } finally {
            File::deleteDirectory($tempDir);
        }
    }

    /**
     * Recursively search for the directory that contains artisan (Laravel root).
     * Handles GitHub-style wrappers (one extra subdirectory) and repos with a
     * nested 'code/' subfolder. Searches up to 3 levels deep.
     */
    private function findLaravelRoot(string $dir, int $depth = 0): ?string
    {
        if ($depth > 3) {
            return null;
        }

        $dir = rtrim($dir, '/') . '/';

        if (File::exists($dir . 'artisan')) {
            return $dir;
        }

        foreach (File::directories($dir) as $sub) {
            $found = $this->findLaravelRoot($sub, $depth + 1);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * Copy every file from $src to $dst, skipping protected paths so that
     * user data (.env, uploads, logs) is never overwritten.
     *
     * Handles the public/ → public_path() remapping for shared hosting
     * where Laravel's public folder is named "public_html" instead of "public".
     *
     * @return array{copied:int, skipped:int, public_mapped:bool}
     */
    private function applyFiles(string $src, string $dst): array
    {
        $src = rtrim($src, '/') . '/';
        $dst = rtrim($dst, '/') . '/';

        // On shared hosting, public_path() may be .../public_html while
        // base_path('public') is .../public. If they differ and the ZIP
        // contains a public/ folder, redirect those files to public_path().
        $publicPath    = rtrim(public_path(), '/') . '/';
        $basePub       = rtrim(base_path('public'), '/') . '/';
        $remapPublic   = ($publicPath !== $basePub) && File::isDirectory($src . 'public');

        // Paths relative to Laravel root that must never be touched
        $protected = [
            '.env',
            'storage/app/public',
            'storage/logs',
            'storage/framework',
            'public/storage',
            'public_html/storage',
            '.git',
            'node_modules',
        ];

        $copied  = 0;
        $skipped = 0;

        foreach (File::allFiles($src) as $file) {
            $relative = ltrim(
                str_replace('\\', '/', str_replace($src, '', $file->getPathname())),
                '/'
            );

            foreach ($protected as $guard) {
                if (str_starts_with($relative, $guard)) {
                    $skipped++;
                    continue 2;
                }
            }

            // Remap public/ → actual public_path() when they differ
            if ($remapPublic && str_starts_with($relative, 'public/')) {
                $dest = $publicPath . substr($relative, 7); // 7 = strlen('public/')
            } else {
                $dest = $dst . $relative;
            }

            $dir = dirname($dest);
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            File::copy($file->getPathname(), $dest);
            $copied++;
        }

        \Log::info("SystemUpdate: {$copied} files copied, {$skipped} skipped. public remapped={$remapPublic}. src={$src} dst={$dst}");

        return ['copied' => $copied, 'skipped' => $skipped, 'public_mapped' => $remapPublic];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLICK & UPDATE  (unchanged — fetches from remote license server)
    // ─────────────────────────────────────────────────────────────────────────

    public function checkUpdate(): array
    {
        $params = [
            'domain'          => url('/'),
            'software_id'     => config('installer.software_id'),
            'version'         => config('installer.version'),
            'purchase_key'    => env('PURCHASE_KEY'),
            'envato_username' => env('ENVATO_USERNAME'),
        ];

        try {
            $response = Http::post(
                'https://verifylicense.online/api/licence-verification/get-update-versions',
                $params
            );
            $data = $response->json();

            if (!isset($data['success'], $data['code'], $data['message'])) {
                return ['success' => false, 'message' => 'Invalid API response structure'];
            }

            if ($data['success'] === true) {
                return [
                    'success' => true,
                    'message' => empty($data['data']) ? 'No updates available' : 'Update available',
                    'data'    => $data['data'] ?? [],
                ];
            }

            $msg = $data['message'] ?? 'Unknown error';
            if (isset($data['data']['errors'])) {
                $msg .= ': ' . json_encode($data['data']['errors']);
            }

            return ['success' => false, 'message' => $msg];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to connect: ' . $e->getMessage()];
        }
    }

    public function installUpdate(Request $request): array
    {
        $params = [
            'domain'          => url('/'),
            'software_id'     => config('installer.software_id'),
            'version'         => $request->input('version'),
            'purchase_key'    => env('PURCHASE_KEY'),
            'envato_username' => env('ENVATO_USERNAME'),
        ];

        $status       = false;
        $message      = 'Update failed';
        $errorMessage = '';

        try {
            $response = Http::post(
                'https://verifylicense.online/api/licence-verification/download-version',
                $params
            );
            $data = $response->json();

            if ($response->successful()) {
                $basePath = storage_path('app/public/temp_update/');
                if (!file_exists($basePath)) {
                    mkdir($basePath, 0777);
                }

                $filename = 'default_update.zip';
                if ($response->hasHeader('Content-Disposition')) {
                    if (preg_match('/filename="(.+?)"/', $response->header('Content-Disposition'), $m)) {
                        $filename = $m[1];
                    }
                }

                $filePath = $basePath . $filename;
                file_put_contents($filePath, $response->body());

                $zip = new ZipArchive;
                if ($zip->open($filePath) !== true) {
                    $this->deleteDirectory($basePath);
                    return ['success' => false, 'message' => translate('Error! Could not open File')];
                }

                $zip->extractTo($basePath);
                $zip->close();

                $configFilePath = $basePath . 'config.json';
                $configJson     = File::exists($configFilePath)
                    ? json_decode(File::get($configFilePath), true)
                    : null;

                if (empty($configJson['version'])) {
                    $this->deleteDirectory($basePath);
                    return ['success' => false, 'message' => translate('Error! No Configuration file found')];
                }

                $newVersion     = (float) $configJson['version'];
                $currentVersion = (float) (site_settings('app_version') ?? 1.1);

                if ($newVersion > $currentVersion) {
                    if ($this->copyDirectory($basePath, dirname(base_path()))) {
                        $this->_runMigrations($configJson);
                        $this->_runSeeder($configJson);
                        DB::table('settings')->upsert([
                            ['key' => 'app_version',        'value' => $newVersion],
                            ['key' => 'system_installed_at','value' => Carbon::now()],
                        ], ['key'], ['value']);
                        $status  = true;
                        $message = translate('Your system updated successfully');
                    }
                }
            }

            $errorMessage = $data['message'] ?? 'Unknown error';
            if (isset($data['data']['errors'])) {
                $errorMessage .= ': ' . json_encode($data['data']['errors']);
            }
            if (isset($data['data']['error'])) {
                $errorMessage = $data['data']['error'];
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }

        optimize_clear();
        $this->deleteDirectory($basePath ?? '');

        return ['success' => $status, 'message' => $status ? $message : $errorMessage];
    }

    public function backupDemo()
    {
        if (is_demo()) {
            return response()->json(['success' => false, 'message' => 'Not available in demo mode.']);
        }

        Artisan::call('demo:backup');
        return response()->json(['success' => true, 'message' => 'Backup completed.', 'output' => Artisan::output()]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers kept for installUpdate compatibility
    // ─────────────────────────────────────────────────────────────────────────

    private function _runMigrations(array $json): void
    {
        foreach ($this->_getFormattedFiles(Arr::get($json, 'migrations', [])) as $migration) {
            Artisan::call('migrate', ['--path' => $migration, '--force' => true]);
        }
    }

    private function _runSeeder(array $json): void
    {
        foreach ($this->_getFormattedFiles(Arr::get($json, 'seeder', [])) as $seeder) {
            Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
        }
    }

    private function _getFormattedFiles(array $files): array
    {
        $current        = (float) site_settings('app_version', 1.1);
        $formattedFiles = [];
        foreach ($files as $version => $file) {
            if (version_compare($version, (string) $current, '>')) {
                $formattedFiles[] = $file;
            }
        }
        return array_unique(Arr::collapse($formattedFiles));
    }

    public function copyDirectory(string $src, string $dst): bool
    {
        try {
            $dir = opendir($src);
            @mkdir($dst);
            while (false !== ($file = readdir($dir))) {
                if ($file !== '.' && $file !== '..') {
                    if (is_dir($src . '/' . $file)) {
                        $this->copyDirectory($src . '/' . $file, $dst . '/' . $file);
                    } else {
                        copy($src . '/' . $file, $dst . '/' . $file);
                    }
                }
            }
            closedir($dir);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deleteDirectory(string $dirname): bool
    {
        if (!$dirname || !is_dir($dirname)) {
            return false;
        }
        try {
            $handle = opendir($dirname);
            if (!$handle) {
                return false;
            }
            while ($file = readdir($handle)) {
                if ($file !== '.' && $file !== '..') {
                    is_dir($dirname . '/' . $file)
                        ? $this->deleteDirectory($dirname . '/' . $file)
                        : unlink($dirname . '/' . $file);
                }
            }
            closedir($handle);
            rmdir($dirname);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function removeDirectory(string $basePath): void
    {
        if (File::exists($basePath)) {
            File::deleteDirectory($basePath);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Traits\InstallerManager;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
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

    public function __construct()
    {

    }

    public function init(): View
    {

        return view('admin.system_update', [
            "title" => translate("Update System")
        ]);
    }


    /**
     * Summary of checkUpdate
     * @return array{data: array, message: string, success: bool|array{data: mixed, message: string, success: bool}|array{message: mixed, success: bool}|array{message: string, success: bool}}
     */
    public function checkUpdate(): array
    {
        $params = [
            'domain' => url('/'),
            'software_id' => config('installer.software_id'),
            'version' => config('installer.version'),
            'purchase_key' => env('PURCHASE_KEY'),
            'envato_username' => env('ENVATO_USERNAME')
        ];

        try {
            $url = 'https://verifylicense.online/api/licence-verification/get-update-versions';
            $response = Http::post($url, $params);
            $data = $response->json();

            if (!isset($data['success'], $data['code'], $data['message'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid API response structure',
                ];
            }

            if ($data['success'] === true) {
                if (!empty($data['data'])) {
                    return [
                        'success' => true,
                        'message' => 'Update available',
                        'data' => $data['data'],
                    ];
                } else {
                    return [
                        'success' => true,
                        'message' => 'No updates available',
                        'data' => [],
                    ];
                }
            }

            $errorMessage = $data['message'] ?? 'Unknown error';
            if (isset($data['data']['errors'])) {
                $errors = $data['data']['errors'];
                $errorMessage .= ': ' . json_encode($errors);
            }

            return [
                'success' => false,
                'message' => $errorMessage,
            ];

        } catch (\Exception $e) {

            return [
                'success' => false,
                'message' => 'Failed to connect to API: ' . $e->getMessage(),
            ];
        }
    }


    public function installUpdate(Request $request)
    {
        $params = [
            'domain' => url('/'),
            'software_id' => config('installer.software_id'),
            'version' => $request->input('version'),
            'purchase_key' => env('PURCHASE_KEY'),
            'envato_username' => env('ENVATO_USERNAME')
        ];

        $status = false;

        try {

            $url = 'https://verifylicense.online/api/licence-verification/download-version';
            $response = Http::post($url, $params);
            $data = $response->json();

            if ($response->successful()) {
                $basePath = base_path('/storage/app/public/temp_update/');
                if (!file_exists($basePath))
                    mkdir($basePath, 0777);


                $filename = 'default_update.zip';
                if ($response->hasHeader('Content-Disposition')) {
                    $disposition = $response->header('Content-Disposition');
                    if (preg_match('/filename="(.+?)"/', $disposition, $matches)) {
                        $filename = $matches[1];
                    }
                }

                $filePath = $basePath . '/' . $filename;

                file_put_contents($filePath, $response->body());

                $zip = new ZipArchive;
                $res = $zip->open($filePath);

                if (!$res) {
                    $this->deleteDirectory($basePath);

                    $updateResponse = [
                        'success' => false,
                        'message' => translate('Error! Could not open File')
                    ];

                    return $updateResponse;

                }


                $zip->extractTo($basePath);

                $zip->close();

                $configFilePath = $basePath . 'config.json';
                $configJson = json_decode(file_get_contents($configFilePath), true);

                if (empty($configJson) || empty($configJson['version'])) {
                    $this->deleteDirectory($basePath);

                    $updateResponse = [
                        'success' => false,
                        'message' => translate('Error! No Configuration file found')
                    ];

                    return $updateResponse;
                }


                $newVersion = (double) $configJson['version'];
                $currentVersion = (double) @site_settings("app_version") ?? 1.1;



                $src = $basePath;
                $dst = dirname(base_path());


                if ($newVersion > $currentVersion) {

                    $message = translate('Your system updated successfully');
                    $status = true;



                    if ($this->copyDirectory($src, $dst)) {

                        $this->_runMigrations($configJson);
                        $this->_runSeeder($configJson);
                        DB::table('settings')->upsert([
                            ['key' => 'app_version', 'value' => $newVersion],
                            ['key' => 'system_installed_at', 'value' => Carbon::now()],
                        ], ['key'], ['value']);


                    }
                }


            }

            $errorMessage = $data['message'] ?? 'Unknown error';
            if (isset($data['data']['errors'])) {
                $errors = $data['data']['errors'];
                $errorMessage .= ': ' . json_encode($errors);
            }

            if (isset($data['data']['error'])) {
                $errorMessage = $data['data']['error'];
            }

        } catch (\Exception $e) {

        }

        $updateResponse = [
            'success' => $status,
            'message' => $message ?? $errorMessage
        ];


        optimize_clear();
        $this->deleteDirectory($basePath);


        return $updateResponse;


    }



    /**
     * update the system
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {

        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        ini_set('max_input_time', '3600');
        ini_set('max_execution_time', '3600');
        ini_set('post_max_size', '2048M');
        ini_set('upload_max_filesize', '2048M');

        $request->validate([
            'updateFile' => ['required', 'mimes:zip', 'max:2097152'], // 2GB max (2097152 KB)
        ], [
            'updateFile.required' => translate('File field is required'),
            'updateFile.max' => translate('File size must not exceed 2GB')
        ]);

        $response = response_status(translate('Your system is currently running the latest version.'), 'error');
        $basePath = storage_path('app/public/temp_update/');

        try {
            if ($request->hasFile('updateFile')) {

                // Clean up any existing temp directory first
                if (File::exists($basePath)) {
                    File::deleteDirectory($basePath);
                }
                File::makeDirectory($basePath, 0755, true);

                $zipFile = $request->file('updateFile');
                $zipPath = $basePath . $zipFile->getClientOriginalName();

                // Move uploaded file with stream to handle large files efficiently
                $zipFile->move($basePath, $zipFile->getClientOriginalName());

                // Validate ZIP file integrity
                $zip = new ZipArchive;
                $res = $zip->open($zipPath);

                if ($res !== true) {
                    File::deleteDirectory($basePath);
                    return back()->with("error", translate('Error! Invalid or corrupted ZIP file'));
                }

                // Extract with validation
                $extractPath = $basePath . 'extracted/';
                File::makeDirectory($extractPath, 0755, true);

                if (!$zip->extractTo($extractPath)) {
                    $zip->close();
                    File::deleteDirectory($basePath);
                    return back()->with("error", translate('Error! Failed to extract files'));
                }
                $zip->close();

                // Read and validate configuration file
                $configFilePath = $extractPath . 'config.json';
                if (!File::exists($configFilePath)) {
                    File::deleteDirectory($basePath);
                    return back()->with("error", translate('Error! No configuration file found'));
                }

                $configJson = json_decode(File::get($configFilePath), true);

                if (empty($configJson) || empty($configJson['version'])) {
                    File::deleteDirectory($basePath);
                    return back()->with("error", translate('Error! Invalid configuration file'));
                }

                $newVersion = (float) $configJson['version'];
                $currentVersion = (float) (site_settings("app_version") ?? 1.1);

                if ($newVersion <= $currentVersion) {
                    File::deleteDirectory($basePath);
                    return back()->with("error", translate('The uploaded version is not newer than current version'));
                }

                // Backup critical files before update
                $this->createBackup();

                // Copy files with Laravel File facade (much faster than recursive copy)
                $dst = dirname(base_path());

                try {
                    $this->copyDirectoryOptimized($extractPath, $dst);

                    // Run migrations and seeders
                    $this->_runMigrations($configJson);
                    $this->_runSeeder($configJson);

                    // Update version in database
                    DB::table('settings')->upsert([
                        ['key' => 'app_version', 'value' => $newVersion],
                        ['key' => 'system_installed_at', 'value' => Carbon::now()],
                    ], ['key'], ['value']);

                    $response = response_status(translate('System updated successfully to version ') . $newVersion);

                } catch (\Exception $e) {
                    \Log::error('Update copy failed: ' . $e->getMessage());
                    // Restore backup if copy fails
                    $this->restoreBackup();
                    throw $e;
                }

            }

        } catch (\Exception $ex) {
            \Log::error('System update failed: ' . $ex->getMessage());
            $response = response_status(translate('Update failed: ') . strip_tags($ex->getMessage()), 'error');
        } finally {
            // Always cleanup temp directory
            if (File::exists($basePath)) {
                File::deleteDirectory($basePath);
            }
        }

        optimize_clear();
        return redirect()->back()->with($response);
    }

    /**
     * Optimized directory copying using Laravel File facade
     * Much faster than recursive copy() calls
     *
     * @param string $src
     * @param string $dst
     * @return bool
     */
    private function copyDirectoryOptimized(string $src, string $dst): bool
    {
        if (!File::isDirectory($src)) {
            return false;
        }

        // Use File::copyDirectory with Laravel's optimized implementation
        if (!File::isDirectory($dst)) {
            File::makeDirectory($dst, 0755, true);
        }

        // Get all files and directories
        $items = File::allFiles($src);

        foreach ($items as $item) {
            $relativePath = str_replace($src, '', $item->getPathname());
            $destPath = $dst . $relativePath;

            // Create directory structure if needed
            $destDir = dirname($destPath);
            if (!File::isDirectory($destDir)) {
                File::makeDirectory($destDir, 0755, true);
            }

            // Copy file
            File::copy($item->getPathname(), $destPath);
        }

        return true;
    }

    /**
     * Create backup of critical files before update
     *
     * @return void
     */
    private function createBackup(): void
    {
        try {
            $backupPath = storage_path('app/backups/');
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }

            $timestamp = Carbon::now()->format('Y-m-d_His');
            $backupFile = $backupPath . "backup_{$timestamp}.zip";

            $zip = new ZipArchive;
            if ($zip->open($backupFile, ZipArchive::CREATE) === true) {
                // Backup critical files
                $criticalPaths = [
                    base_path('.env'),
                    base_path('config/'),
                    storage_path('app/'),
                ];

                foreach ($criticalPaths as $path) {
                    if (File::exists($path)) {
                        if (File::isDirectory($path)) {
                            $this->addDirectoryToZip($zip, $path, basename($path));
                        } else {
                            $zip->addFile($path, basename($path));
                        }
                    }
                }

                $zip->close();
                session(['backup_file' => $backupFile]);
            }
        } catch (\Exception $e) {
            \Log::warning('Backup creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Restore backup if update fails
     *
     * @return void
     */
    private function restoreBackup(): void
    {
        try {
            $backupFile = session('backup_file');
            if ($backupFile && File::exists($backupFile)) {
                $zip = new ZipArchive;
                if ($zip->open($backupFile) === true) {
                    $zip->extractTo(dirname(base_path()));
                    $zip->close();
                }
            }
        } catch (\Exception $e) {
            \Log::error('Backup restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Add directory to ZIP archive
     *
     * @param ZipArchive $zip
     * @param string $path
     * @param string $zipPath
     * @return void
     */
    private function addDirectoryToZip(ZipArchive $zip, string $path, string $zipPath): void
    {
        $files = File::allFiles($path);
        foreach ($files as $file) {
            $relativePath = str_replace($path, '', $file->getPathname());
            $zip->addFile($file->getPathname(), $zipPath . $relativePath);
        }
    }


    private function _runMigrations(array $json): void
    {

        $migrations = Arr::get($json, 'migrations', default: []);
        if (count($migrations) > 0) {
            $migrationFiles = $this->_getFormattedFiles($migrations);
            foreach ($migrationFiles as $migration) {
                Artisan::call(
                    'migrate',
                    array(
                        '--path' => $migration,
                        '--force' => true
                    )
                );
            }
        }
    }

    private function _runSeeder(array $json): void
    {

        $seeders = Arr::get($json, 'seeder', []);

        if (count($seeders) > 0) {
            $seederFiles = $this->_getFormattedFiles($seeders);
            foreach ($seederFiles as $seeder) {
                Artisan::call(
                    'db:seed',
                    array(
                        '--class' => $seeder,
                        '--force' => true
                    )
                );
            }
        }
    }

    private function _getFormattedFiles(array $files): array
    {

        $currentVersion = (double) site_settings(key: "app_version", default: 1.1);
        $formattedFiles = [];
        foreach ($files as $version => $file) {
            if (version_compare($version, (string) $currentVersion, '>')) {
                $formattedFiles[] = $file;
            }
        }

        return array_unique(Arr::collapse($formattedFiles));

    }



    /**
     * Copy directory
     *
     * @param string $src
     * @param string $dst
     * @return boolean
     */
    public function copyDirectory(string $src, string $dst): bool
    {

        try {
            $dir = opendir($src);
            @mkdir($dst);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src . '/' . $file)) {
                        $this->copyDirectory($src . '/' . $file, $dst . '/' . $file);
                    } else {
                        copy($src . '/' . $file, $dst . '/' . $file);
                    }
                }
            }
            closedir($dir);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }



    /**
     * delete directory
     *
     * @param string $dirname
     * @return boolean
     */
    public function deleteDirectory(string $dirname): bool
    {

        try {
            if (!is_dir($dirname)) {
                return false;
            }
            $dir_handle = opendir($dirname);

            if (!$dir_handle)
                return false;
            while ($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (!is_dir($dirname . "/" . $file))
                        unlink($dirname . "/" . $file);
                    else
                        $this->deleteDirectory($dirname . '/' . $file);
                }
            }
            closedir($dir_handle);
            rmdir($dirname);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


    public function removeDirectory($basePath)
    {

        if (File::exists($basePath)) {
            File::deleteDirectory($basePath);
        }
    }


    public function backupDemo()
    {
        if (is_demo()) {
            return response()->json([
                'success' => false,
                'message' => 'This function is not available in demo mode.',
            ]);
        }

        Artisan::call('demo:backup');
        $output = Artisan::output();

        return response()->json([
            'success' => true,
            'message' => 'Backup completed successfully.',
            'output' => $output
        ]);
    }


}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ZipArchive;
use Illuminate\Support\Facades\File;

class DemoBackupCommand extends Command
{
    protected $signature = 'demo:backup {--no-db : Skip database export} {--no-assets : Skip asset zipping}';
    protected $description = 'Backup demo database and assets into kode/resources folder';

    public function handle()
    {
        $this->info('🧩 Starting demo backup...');

        if (!$this->option('no-db')) {
            $this->backupDatabase();
        }

        if (!$this->option('no-assets')) {
            $this->zipAssets();
        }

        $this->info(' Demo backup completed successfully.');
        return Command::SUCCESS;
    }

    protected function backupDatabase()
    {
        $this->info('📦 Exporting database...');

        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');
        $dbName = env('DB_DATABASE');
        $dbHost = env('DB_HOST', '127.0.0.1');
        $filePath = base_path('resources/database/database_demo.sql');

        // Use mysqldump to export DB
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s 2>&1',
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbHost),
            escapeshellarg($dbName),
            escapeshellarg($filePath)
        );


        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            $this->info("Database exported to: resources/database/database_demo.sql");
        } else {
            $this->error("Database export failed. Check credentials or mysqldump availability.");
        }
    }

    protected function zipAssets()
    {
        $this->info('Zipping asset folders...');

        $rootPath = base_path('../assets');
        $zipFile = base_path('resources/data/backup-file.zip');
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Failed to create zip file at $zipFile");
            return;
        }

        $folders = [
            "$rootPath/files" => 'files',
            "$rootPath/images" => 'images',
        ];

        foreach ($folders as $folderPath => $zipPath) {
            if (File::exists($folderPath)) {
                $this->addFolderToZip($folderPath, $zip, $zipPath);
            } else {
                $this->warn("Folder not found: $folderPath");
            }
        }

        $zip->close();
        $this->info("Assets zipped to: resources/data/backup-file.zip");
    }

    protected function addFolderToZip($folder, ZipArchive $zip, $baseInZip)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $baseInZip . '/' . substr($filePath, strlen($folder) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
}

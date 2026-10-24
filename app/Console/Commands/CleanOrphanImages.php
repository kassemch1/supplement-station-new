<?php

namespace App\Console\Commands;

use App\Services\ImageFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CleanOrphanImages extends Command
{
    protected $signature = 'images:clean-orphans
        {--force : Really move the unused files (without it the command only lists them)}
        {--restore= : Move the files of this backup folder name back (e.g. orphans_2026-10-27_120000)}';

    protected $description = 'Move uploaded pictures that nothing in the database uses into storage/app/image_backups (nothing is deleted)';

    // Files this new are skipped: an upload is written to disk a moment before its database row
    private const MIN_AGE_SECONDS = 3600;

    public function handle(): int
    {
        if ($restore = $this->option('restore')) {
            return $this->restore($restore);
        }

        $disk = Storage::disk('public');
        $referenced = ImageFiles::referencedPaths();
        $orphans = [];
        $bytes = 0;

        foreach (ImageFiles::DIRECTORIES as $directory) {
            foreach ($disk->files($directory) as $path) {
                $url = 'storage/'.$path;
                if (isset($referenced[$url]) || str_starts_with(basename($path), '.')
                    || $disk->lastModified($path) > time() - self::MIN_AGE_SECONDS
                    || ImageFiles::mentionedInDescriptions(basename($path))) {
                    continue;
                }
                $orphans[] = $path;
                $bytes += $disk->size($path);
            }
        }

        $size = number_format($bytes / 1048576, 1).' MB';
        if (!$this->option('force')) {
            foreach ($orphans as $path) {
                $this->line($path);
            }
            $this->info(count($orphans)." unused file(s), {$size}. Nothing was moved; run again with --force to move them.");

            return self::SUCCESS;
        }

        $folder = 'orphans_'.now()->format('Y-m-d_His');
        $backup = $this->backupRoot().'/'.$folder;
        foreach ($orphans as $path) {
            File::ensureDirectoryExists(dirname($backup.'/'.$path));
            File::move($disk->path($path), $backup.'/'.$path);
        }

        $this->info('Moved '.count($orphans)." unused file(s) ({$size}) to {$backup}");
        $this->info("Undo: php artisan images:clean-orphans --restore={$folder}");

        return self::SUCCESS;
    }

    private function restore(string $folder): int
    {
        $backup = $this->backupRoot().'/'.basename($folder);
        if (!File::isDirectory($backup)) {
            $this->error("Backup folder not found: {$backup}");

            return self::FAILURE;
        }

        $disk = Storage::disk('public');
        $moved = 0;
        foreach (File::allFiles($backup) as $file) {
            $path = str_replace('\\', '/', $file->getRelativePathname());
            if ($disk->exists($path)) {
                $this->warn("Kept in backup, already exists: {$path}");
                continue;
            }
            File::ensureDirectoryExists(dirname($disk->path($path)));
            File::move($file->getPathname(), $disk->path($path));
            $moved++;
        }

        $this->info("Moved {$moved} file(s) back into storage/app/public.");

        return self::SUCCESS;
    }

    // storage/app is git-ignored and not reachable from the web (only storage/app/public is linked)
    private function backupRoot(): string
    {
        return storage_path('app/image_backups');
    }
}

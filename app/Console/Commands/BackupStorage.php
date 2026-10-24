<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:storage {--prune-old : Delete the old timestamped public_storage_* backup folders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mirror the public storage directory (uploaded images) into storage/backups/public_storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Uploaded files get random, never-reused names, so a mirror only needs the files it doesn't have
        // yet. This used to copy everything into a new timestamped folder every minute and never clean up.
        $source = storage_path('app/public');
        $destination = storage_path('backups/public_storage');

        $copied = 0;
        foreach (File::allFiles($source) as $file) {
            $target = $destination.'/'.$file->getRelativePathname();
            if (!File::exists($target) || File::size($target) !== $file->getSize()) {
                File::ensureDirectoryExists(dirname($target));
                File::copy($file->getPathname(), $target);
                $copied++;
            }
        }

        $this->info("Backup up to date: {$copied} new file(s) copied to {$destination}");

        if ($this->option('prune-old')) {
            $old = File::glob(storage_path('backups/public_storage_*'), GLOB_ONLYDIR);
            foreach ($old as $dir) {
                File::deleteDirectory($dir);
            }
            $this->info('Deleted '.count($old).' old timestamped backup folder(s).');
        }
    }
}

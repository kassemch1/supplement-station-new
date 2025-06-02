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
    protected $signature = 'backup:storage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the public storage directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $source = public_path('storage');
        $destination = storage_path('backups/public_storage_' . date('Y-m-d_H-i-s'));

        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        File::copyDirectory($source, $destination);

        $this->info("Backup completed successfully. Files copied to: $destination");
    }
}

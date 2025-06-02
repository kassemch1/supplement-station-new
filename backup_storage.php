<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

// Define the source and destination paths
$source = public_path('storage');
$destination = storage_path('backups/public_storage_' . date('Y-m-d_H-i-s'));

// Ensure the backup directory exists
if (!File::exists($destination)) {
    File::makeDirectory($destination, 0755, true);
}

// Recursively copy the contents from the source to the destination
File::copyDirectory($source, $destination);

echo "Backup completed successfully. Files copied to: $destination" . PHP_EOL;

<?php

namespace App\Console\Commands;

use App\Services\ImageFiles;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConvertImagesToWebp extends Command
{
    protected $signature = 'images:convert-webp
        {--dry-run : Only show what would be converted}
        {--revert : Put the original PNG/JPG paths back (the WebP files that nothing uses are deleted)}';

    protected $description = 'Convert the product photos and the home banner to WebP. Originals are kept, order lines are not touched.';

    // table => [column, max width/height]
    private const TARGETS = [
        'product_images' => ['url', ImageOptimizer::PRODUCT_MAX_SIZE],
        'banners' => ['image', ImageOptimizer::BANNER_MAX_SIZE],
    ];

    public function handle(): int
    {
        return $this->option('revert') ? $this->revert() : $this->convert();
    }

    private function convert(): int
    {
        if (!ImageOptimizer::supported()) {
            $this->error('This PHP has no GD WebP support, nothing was changed.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');
        $converted = 0;
        $skipped = 0;
        $bytesBefore = 0;
        $bytesAfter = 0;
        // Two rows using the same file get the same WebP
        $done = [];

        foreach (self::TARGETS as $table => [$column, $maxSize]) {
            $rows = DB::table($table)
                ->where(fn ($query) => $query->whereRaw("LOWER($column) LIKE '%.png'")
                    ->orWhereRaw("LOWER($column) LIKE '%.jpg'")
                    ->orWhereRaw("LOWER($column) LIKE '%.jpeg'"))
                ->orderBy('id')
                ->get(['id', $column]);

            foreach ($rows as $row) {
                $oldUrl = $row->$column;
                $oldPath = ImageFiles::diskPath($oldUrl);
                if ($oldPath === null || !$disk->exists($oldPath)) {
                    $this->warn("Skipped {$table} #{$row->id}: file not found ({$oldUrl})");
                    $skipped++;
                    continue;
                }

                $newUrl = $done[$oldUrl] ?? null;
                if ($newUrl === null) {
                    $webp = ImageOptimizer::toWebp($disk->path($oldPath), $maxSize);
                    $oldSize = $disk->size($oldPath);
                    if ($webp === null || strlen($webp) >= $oldSize) {
                        $this->warn("Skipped {$table} #{$row->id}: could not make a smaller WebP ({$oldUrl})");
                        $skipped++;
                        continue;
                    }

                    $newPath = $this->freeWebpPath($oldPath);
                    $newUrl = 'storage/'.$newPath;
                    $bytesBefore += $oldSize;
                    $bytesAfter += strlen($webp);
                    if (!$dryRun) {
                        $disk->put($newPath, $webp);
                    }
                    $done[$oldUrl] = $newUrl;
                }

                if (!$dryRun) {
                    DB::transaction(function () use ($table, $column, $row, $oldUrl, $newUrl) {
                        DB::table('image_conversions')->insert([
                            'table_name' => $table,
                            'row_id' => $row->id,
                            'old_url' => $oldUrl,
                            'new_url' => $newUrl,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        // Query builder on purpose: updated_at of the image rows stays as it was
                        DB::table($table)->where('id', $row->id)->where($column, $oldUrl)->update([$column => $newUrl]);
                    });
                }
                $converted++;
            }
        }

        $mb = fn ($bytes) => number_format($bytes / 1048576, 1).' MB';
        $this->info(($dryRun ? '[dry run] Would convert' : 'Converted')." {$converted} image(s), skipped {$skipped}.");
        $this->info('New files: '.count($done).', '.$mb($bytesBefore).' -> '.$mb($bytesAfter).'. The originals were kept.');

        return self::SUCCESS;
    }

    private function revert(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');
        $restored = 0;
        $missing = 0;

        foreach (DB::table('image_conversions')->orderBy('id')->get() as $conversion) {
            $column = self::TARGETS[$conversion->table_name][0] ?? null;
            $oldPath = ImageFiles::diskPath($conversion->old_url);
            if (!$column || $oldPath === null || !$disk->exists($oldPath)) {
                $this->warn("Kept {$conversion->table_name} #{$conversion->row_id}: original file is missing ({$conversion->old_url})");
                $missing++;
                continue;
            }
            if ($dryRun) {
                $restored++;
                continue;
            }

            DB::transaction(function () use ($conversion, $column) {
                DB::table($conversion->table_name)
                    ->where('id', $conversion->row_id)
                    ->where($column, $conversion->new_url)
                    ->update([$column => $conversion->old_url]);
                DB::table('image_conversions')->where('id', $conversion->id)->delete();
            });
            ImageFiles::deleteIfUnused($conversion->new_url);
            $restored++;
        }

        $this->info(($dryRun ? '[dry run] Would restore' : 'Restored')." {$restored} original path(s)".($missing ? ", {$missing} kept (original missing)" : '').'.');

        return $missing ? self::FAILURE : self::SUCCESS;
    }

    // products_images/abc.png -> products_images/abc.webp, or abc-1.webp if that name is taken
    private function freeWebpPath(string $oldPath): string
    {
        $base = preg_replace('/\.[^.\/]+$/', '', $oldPath);
        $candidate = "{$base}.webp";
        for ($i = 1; Storage::disk('public')->exists($candidate); $i++) {
            $candidate = "{$base}-{$i}.webp";
        }

        return $candidate;
    }
}

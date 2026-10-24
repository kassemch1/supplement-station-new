<?php

namespace App\Console\Commands;

use App\Services\Assets;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use MatthiasMullie\Minify;

class BuildAssets extends Command
{
    protected $signature = 'assets:build {--check : Only report whether the committed files are up to date}';

    protected $description = 'Combine and minify the store CSS/JS bundles listed in config/assets.php';

    public function handle()
    {
        if (!class_exists(Minify\JS::class)) {
            $this->error('matthiasmullie/minify is missing: run this on a development copy (composer install with dev packages).');
            return self::FAILURE;
        }

        $output = base_path(config('assets.output'));
        $manifest = [];
        $stale = [];

        foreach (config('assets.bundles') as $name => $sources) {
            $target = "$output/$name";
            $content = $this->build($sources, $target);
            $manifest[$name] = substr(md5($content), 0, 10);

            if (!File::exists($target) || File::get($target) !== $content) {
                $stale[] = $name;
                if (!$this->option('check')) {
                    File::ensureDirectoryExists(dirname($target));
                    File::put($target, $content);
                }
            }
            $this->line(sprintf('%-14s %7.1f KB', $name, strlen($content) / 1024));
        }

        $json = json_encode($manifest, JSON_PRETTY_PRINT) . "\n";
        if (!File::exists(Assets::manifestPath()) || File::get(Assets::manifestPath()) !== $json) {
            $stale[] = 'manifest.json';
            if (!$this->option('check')) {
                File::put(Assets::manifestPath(), $json);
            }
        }

        if ($this->option('check')) {
            if ($stale) {
                $this->error('Out of date: ' . implode(', ', $stale) . '. Run php artisan assets:build');
                return self::FAILURE;
            }
            $this->info('Built assets are up to date.');
            return self::SUCCESS;
        }

        $this->info($stale ? 'Updated: ' . implode(', ', $stale) : 'Nothing changed.');
        return self::SUCCESS;
    }

    private function build(array $sources, string $target): string
    {
        $isCss = str_ends_with($target, '.css');
        $parts = [];

        foreach ($sources as $source) {
            // Relative url(...) paths need no rewriting: the bundles live in public/assets/build, one
            // folder below public/assets like public/assets/css (resources/assets/css is written that way)
            $path = base_path($source);
            if (str_contains($source, '.min.')) {
                $code = File::get($path);
                $parts[] = $isCss ? $code : rtrim($code) . ';';
                continue;
            }
            if ($isCss) {
                // Colours are left as written: the minifier would turn rgba(...) into the newer
                // "rgb(r g b / a)" form, which older phone browsers ignore together with the whole rule
                $minifier = new class($path) extends Minify\CSS {
                    protected function shortenHexColors($content) { return $content; }
                    protected function convertLegacyColors($content) { return $content; }
                    protected function cleanupModernColors($content) { return $content; }
                };
                $minifier->setImportExtensions([]); // keep images and fonts as files, not inline base64
                $parts[] = $minifier->minify();
            } else {
                $parts[] = rtrim((new Minify\JS($path))->minify(), ';') . ';';
            }
        }

        return implode("\n", $parts) . "\n";
    }
}

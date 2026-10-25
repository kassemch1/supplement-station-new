<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// How the project is kept: route files, what may be in git, what the web server may serve, the scripts
class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    private function tracked(): array
    {
        exec('git -C ' . escapeshellarg(base_path()) . ' ls-files', $files, $status);
        if ($status !== 0) {
            $this->markTestSkipped('not a git checkout');
        }

        return $files;
    }

    public function test_each_feature_keeps_its_own_route_file(): void
    {
        $web = file_get_contents(base_path('routes/web.php'));
        foreach (['products', 'categories', 'admin', 'cart', 'banner', 'products_images_admin', 'plane', 'faq', 'option_admin',
            'product_option_admin', 'order_admin', 'contact', 'review_admin', 'coupons', 'subscribe', 'special_offers'] as $file) {
            $this->assertFileExists(base_path("routes/$file.php"));
            $this->assertStringContainsString("require __DIR__ . '/$file.php';", $web);
        }
    }

    public function test_route_names_are_unique_so_routes_can_be_cached(): void
    {
        $names = collect(app('router')->getRoutes()->getRoutes())->map->getName()->filter();
        $this->assertSame($names->count(), $names->unique()->count());
        $this->assertSame(url('/admin/orders/5'), route('admin.orders.details', 5));
    }

    public function test_no_backups_dumps_or_archives_are_in_git(): void
    {
        $bad = array_filter($this->tracked(), fn ($file) => preg_match('#\.(sql|sql\.gz|zip|tgz|tar|gz)$|^backups/|^storage/backups/#i', $file));
        $this->assertSame([], array_values($bad));
        $this->assertFileDoesNotExist(public_path('storage.zip'));

        $ignore = file_get_contents(base_path('.gitignore'));
        foreach (['/backups', '*.sql', '/public/storage.zip'] as $rule) {
            $this->assertStringContainsString($rule, $ignore);
        }
    }

    public function test_the_web_server_never_serves_logs_and_redirects_www(): void
    {
        $htaccess = file_get_contents(public_path('.htaccess'));
        $this->assertStringContainsString('<FilesMatch "^(error_log|', $htaccess);
        $this->assertStringContainsString('RewriteCond %{HTTP_HOST} ^www\.supplement-station\.com$ [NC]', $htaccess);
        $this->assertFileDoesNotExist(public_path('error_log'));
    }

    public function test_removed_pages_stay_removed(): void
    {
        foreach (['/foo', '/test', '/welcome'] as $url) {
            $this->get($url)->assertNotFound();
        }
    }

    public function test_deploy_scripts_parse_and_keep_their_safety_checks(): void
    {
        foreach (['deploy', 'rollback', 'switch-web-root'] as $script) {
            $path = base_path("scripts/$script.sh");
            $this->assertFileExists($path);
            exec('bash -n ' . escapeshellarg($path) . ' 2>&1', $output, $status);
            if ($status === 127) {
                $this->markTestSkipped('bash is not available');
            }
            $this->assertSame(0, $status, "$script.sh: " . implode("\n", $output));
        }

        $deploy = file_get_contents(base_path('scripts/deploy.sh'));
        $this->assertStringContainsString('git status --porcelain', $deploy, 'refuses to overwrite hand edits');
        $this->assertStringContainsString('mysqldump', $deploy, 'backs up the database first');
        $this->assertStringContainsString('git tag "$TAG"', $deploy, 'tags the running version');
        $this->assertStringContainsString('--ff-only', $deploy);
        $rollback = file_get_contents(base_path('scripts/rollback.sh'));
        $this->assertLessThan(strpos($rollback, 'git reset --keep'), strpos($rollback, 'migrate:rollback --force'), 'migrations roll back before the old code is checked out');
    }
}

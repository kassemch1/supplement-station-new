<?php

use App\Models\User;
use App\Services\ImageFiles;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase2ImagesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function productId(): int
    {
        return DB::table('products')->whereNull('deleted_at')->value('id');
    }

    private function productData(array $extra = []): array
    {
        return array_merge(['name' => 'P2 Test product', 'price' => 10, 'category_id' => DB::table('categories')->value('id'),
            'discount' => 0, 'description' => 'test', 'stock' => 'in-stock'], $extra);
    }

    // A real PNG on the fake public disk, old enough for the orphan cleanup
    private function pngFile(string $path, int $width = 400, int $height = 400): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 30, 30));
        ob_start();
        imagepng($image);
        Storage::disk('public')->put($path, ob_get_clean());
        touch(Storage::disk('public')->path($path), time() - 7200);

        return 'storage/'.$path;
    }

    private function imageRow(string $url): int
    {
        return DB::table('product_images')->insertGetId(['product_id' => $this->productId(), 'url' => $url,
            'created_at' => '2025-01-01 10:00:00', 'updated_at' => '2025-01-01 10:00:00']);
    }

    // ---------- uploads ----------

    public function test_product_upload_is_resized_and_stored_as_webp(): void
    {
        $this->actingAs(User::first())->post('/manageProducts/store', $this->productData([
            'images' => [UploadedFile::fake()->image('big.png', 2400, 1200)],
        ]))->assertOk();

        $url = DB::table('product_images')->orderByDesc('id')->value('url');
        $this->assertMatchesRegularExpression('#^storage/products_images/[A-Za-z0-9]{40}\.webp$#', $url);
        $info = getimagesize(Storage::disk('public')->path(substr($url, 8)));
        $this->assertSame([1200, 600, 'image/webp'], [$info[0], $info[1], $info['mime']]);
    }

    public function test_update_upload_is_webp_and_small_images_are_not_enlarged(): void
    {
        $id = $this->productId();
        $this->actingAs(User::first())->post('/manageProducts/update', $this->productData([
            'product_id' => $id, 'images' => [UploadedFile::fake()->image('small.jpg', 300, 200)],
        ]))->assertOk();

        $url = DB::table('product_images')->where('product_id', $id)->orderByDesc('id')->value('url');
        $info = getimagesize(Storage::disk('public')->path(substr($url, 8)));
        $this->assertSame([300, 200, 'image/webp'], [$info[0], $info[1], $info['mime']]);
    }

    public function test_svg_is_rejected_for_products_and_banners(): void
    {
        $svg = UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $admin = $this->actingAs(User::first());
        $admin->postJson('/manageProducts/store', $this->productData(['images' => [$svg]]))->assertStatus(422);
        $admin->postJson('/manageBanner/store', ['product_id' => $this->productId(), 'image' => $svg])->assertStatus(422);
    }

    public function test_new_banner_is_webp_and_old_banner_file_is_removed(): void
    {
        $banner = DB::table('banners')->first();
        $old = $this->pngFile('banner_picture/old-banner.png');
        DB::table('banners')->where('id', $banner->id)->update(['image' => $old]);

        $this->actingAs(User::first())->post('/manageBanner/store', [
            'product_id' => $banner->product_id ?? $this->productId(),
            'image' => UploadedFile::fake()->image('hero.png', 3000, 3000),
        ])->assertOk();

        $new = DB::table('banners')->where('id', $banner->id)->value('image');
        $this->assertStringEndsWith('.webp', $new);
        $this->assertSame(1920, getimagesize(Storage::disk('public')->path(substr($new, 8)))[0]);
        Storage::disk('public')->assertMissing('banner_picture/old-banner.png');
    }

    // ---------- deleting a product image ----------

    public function test_deleting_an_image_row_deletes_its_unused_file(): void
    {
        $url = $this->pngFile('products_images/p2-unused.png');
        $id = $this->imageRow($url);

        $this->actingAs(User::first())->delete("/productImage/{$id}")->assertRedirect();

        $this->assertNull(DB::table('product_images')->find($id));
        Storage::disk('public')->assertMissing('products_images/p2-unused.png');
    }

    public function test_deleting_an_image_keeps_the_file_an_order_line_shows(): void
    {
        $url = $this->pngFile('products_images/p2-ordered.png');
        $id = $this->imageRow($url);
        $line = DB::table('order_items')->value('id');
        DB::table('order_items')->where('id', $line)->update(['product_image' => $url]);

        $this->actingAs(User::first())->delete("/productImage/{$id}")->assertRedirect();

        Storage::disk('public')->assertExists('products_images/p2-ordered.png');
    }

    public function test_only_upload_folders_can_be_deleted(): void
    {
        $this->assertNull(ImageFiles::diskPath('storage/../.env'));
        $this->assertNull(ImageFiles::diskPath('assets/img/logo.png'));
        $this->assertNull(ImageFiles::diskPath('storage/other/x.png'));
        $this->assertSame('products_images/a.png', ImageFiles::diskPath('storage/products_images/a.png'));
    }

    // ---------- converting existing images ----------

    public function test_convert_command_is_reversible_and_keeps_originals_and_order_lines(): void
    {
        $url = $this->pngFile('products_images/p2-convert.png', 1600, 1600);
        $id = $this->imageRow($url);
        $line = DB::table('order_items')->value('id');
        DB::table('order_items')->where('id', $line)->update(['product_image' => $url]);
        $before = DB::table('product_images')->find($id);

        $this->artisan('images:convert-webp', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame($url, DB::table('product_images')->find($id)->url);
        Storage::disk('public')->assertMissing('products_images/p2-convert.webp');

        $this->artisan('images:convert-webp')->assertSuccessful();
        $after = DB::table('product_images')->find($id);
        $this->assertSame('storage/products_images/p2-convert.webp', $after->url);
        $this->assertSame($before->updated_at, $after->updated_at);
        $this->assertSame(1200, getimagesize(Storage::disk('public')->path('products_images/p2-convert.webp'))[0]);
        Storage::disk('public')->assertExists('products_images/p2-convert.png');
        $this->assertSame($url, DB::table('order_items')->where('id', $line)->value('product_image'));
        $this->assertTrue(DB::table('image_conversions')->where(['table_name' => 'product_images', 'row_id' => $id, 'old_url' => $url])->exists());

        // a second run has nothing left to do for this row
        $this->artisan('images:convert-webp')->assertSuccessful();
        $this->assertSame(1, DB::table('image_conversions')->where('row_id', $id)->where('table_name', 'product_images')->count());

        $this->artisan('images:convert-webp', ['--revert' => true]);
        $this->assertSame($url, DB::table('product_images')->find($id)->url);
        $this->assertSame($before->updated_at, DB::table('product_images')->find($id)->updated_at);
        $this->assertFalse(DB::table('image_conversions')->where('row_id', $id)->where('table_name', 'product_images')->exists());
        Storage::disk('public')->assertMissing('products_images/p2-convert.webp');
        Storage::disk('public')->assertExists('products_images/p2-convert.png');
    }

    public function test_deleting_a_converted_image_also_removes_its_unused_original(): void
    {
        $url = $this->pngFile('products_images/p2-conv-del.png');
        $id = $this->imageRow($url);
        $this->artisan('images:convert-webp')->assertSuccessful();

        $this->actingAs(User::first())->delete("/productImage/{$id}")->assertRedirect();

        Storage::disk('public')->assertMissing('products_images/p2-conv-del.webp');
        Storage::disk('public')->assertMissing('products_images/p2-conv-del.png');
        $this->assertFalse(DB::table('image_conversions')->where('row_id', $id)->where('table_name', 'product_images')->exists());
    }

    // ---------- orphan cleanup ----------

    public function test_orphan_cleanup_moves_only_unused_files_and_can_restore_them(): void
    {
        $this->imageRow($this->pngFile('products_images/p2-used.png'));
        $ordered = $this->pngFile('products_images/p2-order-only.png');
        DB::table('order_items')->where('id', DB::table('order_items')->value('id'))->update(['product_image' => $ordered]);
        $this->pngFile('products_images/p2-in-description.png');
        DB::table('products')->where('id', $this->productId())->update(['description' => 'see <img src="/storage/products_images/p2-in-description.png">']);
        $this->pngFile('products_images/p2-orphan.png');
        $this->pngFile('banner_picture/p2-orphan-banner.png');
        Storage::disk('public')->put('products_images/p2-just-uploaded.png', 'x');

        $this->artisan('images:clean-orphans')->assertSuccessful();
        Storage::disk('public')->assertExists('products_images/p2-orphan.png');

        $this->artisan('images:clean-orphans', ['--force' => true])->assertSuccessful();
        foreach (['p2-used', 'p2-order-only', 'p2-in-description', 'p2-just-uploaded'] as $kept) {
            Storage::disk('public')->assertExists("products_images/{$kept}.png");
        }
        Storage::disk('public')->assertMissing('products_images/p2-orphan.png');
        Storage::disk('public')->assertMissing('banner_picture/p2-orphan-banner.png');

        $folder = collect(File::directories(storage_path('app/image_backups')))->map(fn ($d) => basename($d))->sort()->last();
        try {
            $this->assertFileExists(storage_path("app/image_backups/{$folder}/products_images/p2-orphan.png"));
            $this->artisan('images:clean-orphans', ['--restore' => $folder])->assertSuccessful();
            Storage::disk('public')->assertExists('products_images/p2-orphan.png');
            Storage::disk('public')->assertExists('banner_picture/p2-orphan-banner.png');
        } finally {
            File::deleteDirectory(storage_path("app/image_backups/{$folder}"));
        }
    }

    // ---------- pages ----------

    public function test_hero_and_main_product_image_are_not_lazy(): void
    {
        $home = $this->get('/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<img src="[^"]*"[^>]*fetchpriority="high"(?![^>]*loading="lazy")#', $home);

        $id = DB::table('product_images')->join('products', 'products.id', '=', 'product_images.product_id')
            ->whereNull('products.deleted_at')->value('products.id');
        $page = $this->get($this->productUrl($id))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#width="500" height="500"\s+fetchpriority="high"#', $page);
    }
}

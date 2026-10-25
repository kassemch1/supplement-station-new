<?php

namespace Tests\Feature;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// images:convert-webp and images:clean-orphans, both reversible
class ImageCommandsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function pngFile(string $path, int $size = 400): string
    {
        $image = imagecreatetruecolor($size, $size);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 30, 30));
        ob_start();
        imagepng($image);
        Storage::disk('public')->put($path, ob_get_clean());
        touch(Storage::disk('public')->path($path), time() - 7200);

        return 'storage/' . $path;
    }

    private function imageRow(string $url): ProductImage
    {
        $image = ProductImage::factory()->create(['url' => $url]);
        DB::table('product_images')->where('id', $image->id)->update(['updated_at' => '2025-01-01 10:00:00']);

        return $image->fresh();
    }

    public function test_conversion_is_reversible_and_keeps_originals_and_order_lines(): void
    {
        $url = $this->pngFile('products_images/convert.png', 1600);
        $image = $this->imageRow($url);
        $line = OrderItem::factory()->create(['product_image' => $url]);

        $this->artisan('images:convert-webp', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame($url, $image->fresh()->url);
        Storage::disk('public')->assertMissing('products_images/convert.webp');

        $this->artisan('images:convert-webp')->assertSuccessful();
        $after = $image->fresh();
        $this->assertSame('storage/products_images/convert.webp', $after->url);
        $this->assertEquals($image->updated_at, $after->updated_at, 'the row keeps its date');
        $this->assertSame(1200, getimagesize(Storage::disk('public')->path('products_images/convert.webp'))[0]);
        Storage::disk('public')->assertExists('products_images/convert.png');
        $this->assertSame($url, $line->fresh()->product_image, 'past orders keep their picture');
        $this->assertTrue(DB::table('image_conversions')->where(['table_name' => 'product_images', 'row_id' => $image->id, 'old_url' => $url])->exists());

        $this->artisan('images:convert-webp')->assertSuccessful();
        $this->assertSame(1, DB::table('image_conversions')->where('row_id', $image->id)->count(), 'a second run has nothing to do');

        $this->artisan('images:convert-webp', ['--revert' => true]);
        $this->assertSame($url, $image->fresh()->url);
        $this->assertFalse(DB::table('image_conversions')->where('row_id', $image->id)->exists());
        Storage::disk('public')->assertMissing('products_images/convert.webp');
        Storage::disk('public')->assertExists('products_images/convert.png');
    }

    public function test_deleting_a_converted_picture_also_removes_its_unused_original(): void
    {
        $image = $this->imageRow($this->pngFile('products_images/conv-del.png'));
        // A product keeps at least one picture, so it has another one
        ProductImage::create(['product_id' => $image->product_id, 'url' => $this->pngFile('products_images/conv-keep.png')]);
        $this->artisan('images:convert-webp')->assertSuccessful();

        $this->admin()->delete("/productImage/{$image->id}")->assertRedirect();

        Storage::disk('public')->assertMissing('products_images/conv-del.webp');
        Storage::disk('public')->assertMissing('products_images/conv-del.png');
        $this->assertFalse(DB::table('image_conversions')->where('row_id', $image->id)->exists());
    }

    public function test_orphan_cleanup_moves_only_unused_files_and_can_put_them_back(): void
    {
        $this->imageRow($this->pngFile('products_images/used.png'));
        OrderItem::factory()->create(['product_image' => $this->pngFile('products_images/order-only.png')]);
        $this->pngFile('products_images/in-description.png');
        Product::factory()->create(['description' => 'see <img src="/storage/products_images/in-description.png">']);
        $this->pngFile('products_images/orphan.png');
        $this->pngFile('banner_picture/orphan-banner.png');
        Storage::disk('public')->put('products_images/just-uploaded.png', 'x');

        $this->artisan('images:clean-orphans')->assertSuccessful();   // only a report without --force
        Storage::disk('public')->assertExists('products_images/orphan.png');

        $before = File::isDirectory(storage_path('app/image_backups')) ? File::directories(storage_path('app/image_backups')) : [];
        $this->artisan('images:clean-orphans', ['--force' => true])->assertSuccessful();
        foreach (['used', 'order-only', 'in-description', 'just-uploaded'] as $kept) {
            Storage::disk('public')->assertExists("products_images/{$kept}.png");
        }
        Storage::disk('public')->assertMissing('products_images/orphan.png');
        Storage::disk('public')->assertMissing('banner_picture/orphan-banner.png');

        $folder = collect(File::directories(storage_path('app/image_backups')))->diff($before)->map(fn ($d) => basename($d))->first();
        try {
            $this->assertNotNull($folder);
            $this->assertFileExists(storage_path("app/image_backups/{$folder}/products_images/orphan.png"));
            $this->artisan('images:clean-orphans', ['--restore' => $folder])->assertSuccessful();
            Storage::disk('public')->assertExists('products_images/orphan.png');
            Storage::disk('public')->assertExists('banner_picture/orphan-banner.png');
        } finally {
            if ($folder) {
                File::deleteDirectory(storage_path("app/image_backups/{$folder}"));
            }
        }
    }
}

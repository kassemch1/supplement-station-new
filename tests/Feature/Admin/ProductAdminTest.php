<?php

namespace Tests\Feature\Admin;

use App\Models\Banner;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SpecialOffer;
use App\Services\ImageFiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function form(array $extra = []): array
    {
        return array_merge(['name' => 'Test Whey', 'price' => 50, 'category_id' => Category::factory()->create()->id,
            'discount' => 10, 'description' => 'Good protein', 'stock' => 'in-stock'], $extra);
    }

    private function storedPath(string $url): string
    {
        return Storage::disk('public')->path(substr($url, strlen('storage/')));
    }

    // A real PNG on the fake public disk, 2 hours old (older than the orphan cleanup's grace period)
    private function pngFile(string $path, int $size = 400): string
    {
        $image = imagecreatetruecolor($size, $size);
        ob_start();
        imagepng($image);
        Storage::disk('public')->put($path, ob_get_clean());
        touch(Storage::disk('public')->path($path), time() - 7200);

        return 'storage/' . $path;
    }

    // ---------- create and edit ----------

    public function test_a_new_product_is_saved_with_resized_webp_pictures(): void
    {
        $this->admin()->post('/manageProducts/store', $this->form([
            'images' => [UploadedFile::fake()->image('big.png', 2400, 1200), UploadedFile::fake()->image('b.jpg', 300, 200)],
        ]))->assertOk();

        $product = Product::sole();
        $this->assertEquals(['Test Whey', 'test-whey', true], [$product->name, $product->slug, $product->stock]);
        $this->assertEquals(45, $product->price_after_discount);
        [$big, $small] = $product->images->pluck('url')->all();
        $this->assertMatchesRegularExpression('#^storage/products_images/[A-Za-z0-9]{40}\.webp$#', $big);
        $info = getimagesize($this->storedPath($big));
        $this->assertSame([1200, 600, 'image/webp'], [$info[0], $info[1], $info['mime']]);
        $this->assertSame([300, 200], array_slice(getimagesize($this->storedPath($small)), 0, 2), 'small pictures are not enlarged');
    }

    public function test_the_form_is_validated(): void
    {
        $this->admin();
        $svg = UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        $this->postJson('/manageProducts/store', $this->form(['images' => [$svg]]))->assertJsonValidationErrors('images.0');
        $this->postJson('/manageProducts/store', $this->form())->assertJsonValidationErrors(['images' => 'At least one image is required.']);
        $this->postJson('/manageProducts/store', $this->form(['category_id' => 999, 'stock' => 'maybe', 'price' => 0, 'images' => [UploadedFile::fake()->image('a.png')]]))
            ->assertJsonValidationErrors(['category_id', 'stock', 'price']);
        $this->assertSame(0, Product::count());
    }

    public function test_editing_keeps_the_pictures_and_adds_new_ones(): void
    {
        $product = Product::factory()->withImage()->create(['name' => 'Old Name']);

        $this->admin()->postJson('/manageProducts/update', $this->form(['product_id' => $product->id, 'name' => 'New Name', 'stock' => 'out-of-stock']))
            ->assertOk()->assertJson(['success' => 'Product updated successfully']);
        $product->refresh();
        $this->assertEquals(['New Name', 'new-name', false], [$product->name, $product->slug, $product->stock]);
        $this->assertSame(1, $product->images()->count(), 'no picture needed when it already has one');

        $this->postJson('/manageProducts/update', $this->form(['product_id' => $product->id, 'images' => [UploadedFile::fake()->image('c.png')]]))->assertOk();
        $this->assertSame(2, $product->images()->count());

        $product->images()->delete();
        $this->postJson('/manageProducts/update', $this->form(['product_id' => $product->id]))->assertJsonValidationErrors('images');
    }

    // ---------- archiving ----------

    public function test_archiving_keeps_order_history_and_takes_the_product_off_the_shop(): void
    {
        Mail::fake();
        $product = Product::factory()->withImage()->create();
        $this->addToCart($product, [], 2)->assertOk();
        $orderId = $this->placeOrder()->assertOk()->json('order_id');
        $this->flushSession();
        $this->addToCart($product)->assertOk();
        SpecialOffer::create(['product_id' => $product->id, 'position' => 1]);
        $banner = Banner::factory()->create(['product_id' => $product->id]);

        $this->admin()->deleteJson('/manageProducts/destroy', ['id' => $product->id])->assertOk();

        $this->assertSoftDeleted($product);
        $this->assertSame(1, OrderItem::where('order_id', $orderId)->count());
        $this->assertSame(0, CartItem::count());
        $this->assertSame(0, SpecialOffer::count());
        $this->assertNull($banner->fresh()->product_id);
        $this->get('/shop?shop-search=' . urlencode($product->name))->assertDontSee($product->name);
        $this->get("/admin/orders/$orderId")->assertOk()->assertSee($product->name);
    }

    public function test_archived_products_are_listed_and_can_be_restored(): void
    {
        $product = Product::factory()->withImage()->create(['name' => 'Comeback Whey']);
        $product->delete();

        $this->admin()->get('/manageProducts?stock=archived')->assertOk()->assertSee('Comeback Whey')->assertSee('Restore');
        $this->get('/manageProducts')->assertDontSee('Comeback Whey');
        $this->post("/manageProducts/{$product->id}/restore")->assertRedirect('/manageProducts?stock=archived');

        $this->assertNotSoftDeleted($product);
        $this->get($product->url())->assertOk();
    }

    public function test_an_order_line_survives_its_product_being_removed_for_good(): void
    {
        $line = OrderItem::factory()->create(['product_name' => 'Vintage Creatine']);
        Product::withTrashed()->find($line->product_id)->forceDelete();

        $line->refresh();
        $this->assertNull($line->product_id);
        $this->admin()->get("/admin/orders/{$line->order_id}")->assertOk()->assertSee('Vintage Creatine');
    }

    public function test_order_details_take_the_same_queries_for_any_number_of_lines(): void
    {
        $count = function (int $lines) {
            $order = Order::factory()->placed()->create();
            OrderItem::factory()->count($lines)->create(['order_id' => $order->id, 'product_image' => null]);
            Product::all()->each(fn ($p) => ProductImage::factory()->create(['product_id' => $p->id]));
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get("/admin/orders/{$order->id}")->assertOk();

            return count(DB::getQueryLog());
        };

        $this->admin();
        $this->assertSame($count(1), $count(8));
    }

    public function test_a_category_with_products_cannot_be_deleted(): void
    {
        $product = Product::factory()->create();
        $empty = Category::factory()->create();

        $this->admin()->deleteJson('/manageCategories/destroy', ['id' => $product->category_id])->assertStatus(422)
            ->assertJsonPath('message', 'This category still has 1 product(s). Move them to another category or delete them first.');
        $this->deleteJson('/manageCategories/destroy', ['id' => $empty->id])->assertOk();
        $this->assertModelMissing($empty);
    }

    // ---------- pictures ----------

    public function test_deleting_a_picture_deletes_its_file_unless_an_order_shows_it(): void
    {
        $product = Product::factory()->create();
        $unused = ProductImage::create(['product_id' => $product->id, 'url' => $this->pngFile('products_images/unused.png')]);
        $ordered = ProductImage::create(['product_id' => $product->id, 'url' => $this->pngFile('products_images/ordered.png')]);
        OrderItem::factory()->create(['product_image' => $ordered->url]);

        $this->admin()->get("/productImage/{$unused->id}/destroy")->assertNotFound();
        $this->delete("/productImage/{$unused->id}")->assertRedirect();
        $this->delete("/productImage/{$ordered->id}")->assertRedirect();

        $this->assertModelMissing($unused);
        Storage::disk('public')->assertMissing('products_images/unused.png');
        Storage::disk('public')->assertExists('products_images/ordered.png');
    }

    public function test_only_upload_folders_can_be_deleted(): void
    {
        $this->assertNull(ImageFiles::diskPath('storage/../.env'));
        $this->assertNull(ImageFiles::diskPath('assets/img/logo.png'));
        $this->assertNull(ImageFiles::diskPath('storage/other/x.png'));
        $this->assertSame('products_images/a.png', ImageFiles::diskPath('storage/products_images/a.png'));
    }

    public function test_banner_upload_is_webp_and_replaces_the_old_file(): void
    {
        $product = Product::factory()->create();
        $this->admin()->postJson('/manageBanner/store', ['product_id' => $product->id])->assertJsonValidationErrors('image');

        $old = $this->pngFile('banner_picture/old-banner.png');
        $banner = Banner::factory()->create(['image' => $old, 'product_id' => $product->id]);
        $this->post('/manageBanner/store', ['product_id' => $product->id, 'image' => UploadedFile::fake()->image('hero.png', 3000, 3000)])->assertOk();

        $new = $banner->fresh()->image;
        $this->assertStringEndsWith('.webp', $new);
        $this->assertSame(1920, getimagesize($this->storedPath($new))[0]);
        Storage::disk('public')->assertMissing('banner_picture/old-banner.png');

        // changing only the product keeps the picture
        $other = Product::factory()->create();
        $this->post('/manageBanner/store', ['product_id' => $other->id])->assertOk();
        $this->assertSame([$other->id, $new], [$banner->fresh()->product_id, $banner->fresh()->image]);

        $svg = UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        $this->postJson('/manageBanner/store', ['product_id' => $product->id, 'image' => $svg])->assertStatus(422);
    }
}

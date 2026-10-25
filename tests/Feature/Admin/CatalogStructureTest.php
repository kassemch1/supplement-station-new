<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Option;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// Admin side of brands, subcategories, category icons/pictures and option pictures, and the
// migration that fills them on the live data
class CatalogStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_pages_open(): void
    {
        $brand = Brand::create(['name' => 'Dymatize']);
        $category = Category::create(['name' => 'Protein']);
        $this->admin();
        foreach (['/manageBrands', '/manageBrands/create', "/manageBrands/{$brand->id}/edit", '/manageCategories',
                     '/manageCategories/create', "/manageCategories/{$category->id}/edit", '/manageProducts/create'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_brand_with_logo_saved_as_webp_then_replaced_and_deleted(): void
    {
        $this->admin()->post('/manageBrands/store', ['name' => 'Optimum Nutrition', 'logo' => UploadedFile::fake()->image('on.png', 900, 300)])
            ->assertOk()->assertJsonPath('success', true);
        $brand = Brand::firstOrFail();
        $this->assertSame('optimum-nutrition', $brand->slug);
        $this->assertStringEndsWith('.webp', $brand->logo);
        $first = substr($brand->logo, strlen('storage/'));
        Storage::disk('public')->assertExists($first);
        $this->assertLessThanOrEqual(400, getimagesize(Storage::disk('public')->path($first))[0], 'shrunk to the logo size');

        $this->post("/manageBrands/{$brand->id}", ['name' => 'Optimum Nutrition', 'logo' => UploadedFile::fake()->image('new.jpg', 200, 100)])->assertOk();
        Storage::disk('public')->assertMissing($first);

        $this->postJson('/manageBrands/store', ['name' => 'optimum nutrition'])->assertStatus(422);

        $product = Product::factory()->create(['brand_id' => $brand->id]);
        $logo = substr($brand->fresh()->logo, strlen('storage/'));
        $this->deleteJson("/manageBrands/{$brand->id}")->assertOk();
        $this->assertNull($product->fresh()->brand_id, 'the product stays, without a brand');
        Storage::disk('public')->assertMissing($logo);
    }

    public function test_subcategories_icons_and_pictures(): void
    {
        $protein = Category::create(['name' => 'Protein']);
        $this->admin()->post('/manageCategories/store', [
            'name' => 'Whey', 'parent_id' => $protein->id, 'icon' => 'whey', 'sort_order' => 2,
            'image' => UploadedFile::fake()->image('whey.png', 800, 800),
        ])->assertOk()->assertJsonPath('status', 'success');

        $whey = Category::where('name', 'Whey')->firstOrFail();
        $this->assertSame($protein->id, $whey->parent_id);
        $this->assertSame('whey', $whey->icon);
        $this->assertStringEndsWith('.webp', $whey->image);

        // Two levels only
        $this->postJson('/manageCategories/store', ['name' => 'Deep', 'parent_id' => $whey->id])->assertStatus(422);
        $this->postJson('/manageCategories/store', ['name' => 'Bad icon', 'icon' => 'no-such-icon'])->assertStatus(422);
        $this->postJson('/manageCategories/update', ['_method' => 'PUT', 'category_id' => $protein->id, 'name' => 'Protein', 'parent_id' => Category::create(['name' => 'Other'])->id])
            ->assertJsonPath('status', 'exists');
        $this->postJson('/manageCategories/update', ['_method' => 'PUT', 'category_id' => $whey->id, 'name' => 'Whey', 'parent_id' => $whey->id])->assertStatus(422);

        // A main category with subcategories can't be deleted; the shop menu shows the tree
        $this->deleteJson('/manageCategories/destroy', ['id' => $protein->id])->assertStatus(422);
        Product::factory()->withImage()->create(['category_id' => $whey->id]);
        $this->get('/manageCategories')->assertOk()->assertSee('↳', false);
        $this->get('/shop/protein')->assertOk()->assertSee('All Protein');

        // Removing the picture deletes the file
        $file = substr($whey->image, strlen('storage/'));
        $this->post('/manageCategories/update', ['_method' => 'PUT', 'category_id' => $whey->id, 'name' => 'Whey', 'parent_id' => $protein->id, 'remove_image' => 1])->assertOk();
        $this->assertNull($whey->fresh()->image);
        Storage::disk('public')->assertMissing($file);
    }

    public function test_product_form_saves_the_brand(): void
    {
        $category = Category::create(['name' => 'Creatine']);
        $brand = Brand::create(['name' => 'BioTechUSA']);
        $this->admin()->post('/manageProducts/store', [
            'name' => 'Crea Zero', 'price' => 30, 'category_id' => $category->id, 'brand_id' => $brand->id, 'discount' => 0,
            'description' => 'Creatine', 'stock' => 'in-stock', 'images' => [UploadedFile::fake()->image('a.png')],
        ])->assertOk();
        $product = Product::where('name', 'Crea Zero')->firstOrFail();
        $this->assertSame($brand->id, $product->brand_id);
        $this->get("/manageProducts/{$product->id}/edit")->assertOk()->assertSee('BioTechUSA');

        $this->post('/manageProducts/update', [
            'product_id' => $product->id, 'name' => 'Crea Zero', 'price' => 30, 'category_id' => $category->id, 'brand_id' => '',
            'discount' => 0, 'description' => 'Creatine', 'stock' => 'in-stock',
        ])->assertOk();
        $this->assertNull($product->fresh()->brand_id);
        $this->postJson('/manageProducts/update', ['product_id' => $product->id, 'brand_id' => 99999] + $product->only('name', 'price', 'category_id', 'discount', 'description') + ['stock' => 'in-stock'])
            ->assertStatus(422)->assertJsonValidationErrors('brand_id');
    }

    public function test_option_picture_is_kept_while_an_order_shows_it(): void
    {
        $product = Product::factory()->withImage()->create();
        $option = Option::firstOrCreate(['option_name' => 'Flavor']);
        $this->admin()->post('/manageProductsOptions/store', [
            'product_id' => $product->id, 'option_id' => $option->id, 'option_value' => 'Vanilla', 'stock' => 1,
            'image' => UploadedFile::fake()->image('vanilla.jpg', 1500, 1500),
        ])->assertOk();
        $value = ProductOption::firstOrFail();
        $this->assertStringStartsWith('storage/product_options/', $value->image);
        $this->assertStringEndsWith('.webp', $value->image);
        $path = substr($value->image, strlen('storage/'));
        $this->assertLessThanOrEqual(600, getimagesize(Storage::disk('public')->path($path))[0]);

        // Editing without a new file keeps the picture
        $this->post("/manageProductsOptions/{$value->id}", ['_method' => 'PUT', 'option_id' => $option->id, 'option_value' => 'Vanilla Ice', 'stock' => 1])->assertOk();
        $this->assertSame($value->image, $value->fresh()->image);

        // An order line shows it, so deleting the option leaves the file
        OrderItem::factory()->create(['product_image' => $value->image]);
        $this->deleteJson("/manageProductsOptions/{$value->id}")->assertOk();
        Storage::disk('public')->assertExists($path);
    }

    public function test_an_option_picture_on_a_white_background_is_cut_out(): void
    {
        $product = Product::factory()->withImage()->create();
        $option = Option::firstOrCreate(['option_name' => 'Flavor']);
        $tub = imagecreatetruecolor(300, 300);
        imagefill($tub, 0, 0, imagecolorallocate($tub, 255, 255, 255));
        imagefilledrectangle($tub, 80, 60, 220, 260, imagecolorallocate($tub, 120, 40, 20));
        ob_start();
        imagejpeg($tub, null, 85);

        $this->admin()->post('/manageProductsOptions/store', [
            'product_id' => $product->id, 'option_id' => $option->id, 'option_value' => 'Chocolate', 'stock' => 1,
            'image' => UploadedFile::fake()->createWithContent('tub.jpg', ob_get_clean()),
        ])->assertOk();

        $stored = imagecreatefromwebp(Storage::disk('public')->path(substr(ProductOption::sole()->image, strlen('storage/'))));
        $this->assertSame(127, (imagecolorat($stored, 0, 0) >> 24) & 0x7F, 'transparent around the tub');
        $this->assertSame(0, (imagecolorat($stored, 150, 150) >> 24) & 0x7F, 'the tub itself');
    }

    public function test_every_saved_picture_has_its_own_remove_button_and_route(): void
    {
        $this->admin();
        $put = function (string $path) {
            Storage::disk('public')->put($path, 'x');

            return 'storage/' . $path;
        };
        $brand = Brand::create(['name' => 'Logo Brand', 'logo' => $put('brands/logo.webp')]);
        $category = Category::create(['name' => 'Pictured', 'image' => $put('categories/pic.webp')]);
        $product = Product::factory()->withImage()->create();
        $option = Option::firstOrCreate(['option_name' => 'Flavor']);
        $value = ProductOption::create(['product_id' => $product->id, 'option_id' => $option->id, 'option_value' => 'Mango', 'stock' => 1,
            'image' => $put('product_options/mango.webp')]);

        // Each edit form shows the picture with a × wired to its own route
        foreach ([
            ["/manageBrands/{$brand->id}/edit", route('manageBrands.removeLogo', $brand->id)],
            ["/manageCategories/{$category->id}/edit", route('manageCategories.removeImage', $category->id)],
            ["/manageProductsOptions/{$value->id}/edit", route('manageProductOptions.removeImage', $value->id)],
            ["/manageProducts/{$product->id}/edit", route('productImage.destroy', $product->images->first()->id)],
        ] as [$page, $url]) {
            $this->get($page)->assertOk()->assertSee('data-image-remove="' . $url . '"', false)->assertDontSee('name="remove_', false);
        }

        $this->deleteJson("/manageBrands/{$brand->id}/logo")->assertOk()->assertJsonPath('message', 'Logo removed.');
        $this->assertNull($brand->fresh()->logo);
        Storage::disk('public')->assertMissing('brands/logo.webp');

        $this->deleteJson("/manageCategories/{$category->id}/image")->assertOk();
        $this->assertNull($category->fresh()->image);
        Storage::disk('public')->assertMissing('categories/pic.webp');

        // An order line shows the flavour picture: the option loses it, the file stays for the order
        OrderItem::factory()->create(['product_image' => $value->image]);
        $this->deleteJson("/manageProductsOptions/{$value->id}/image")->assertOk();
        $this->assertNull($value->fresh()->image);
        Storage::disk('public')->assertExists('product_options/mango.webp');

        // A picture already gone, and only for signed-in admins
        $this->deleteJson("/manageBrands/{$brand->id}/logo")->assertOk();
        $this->deleteJson('/manageBrands/999999/logo')->assertNotFound();
        $this->post('/logout');
        $this->deleteJson("/manageCategories/{$category->id}/image")->assertUnauthorized();
    }

    public function test_the_fill_migration_builds_the_tree_and_brands_and_rolls_back_exactly(): void
    {
        $migration = require database_path('migrations/2026_10_29_000001_fill_categories_and_brands.php');
        $migration->down();   // the empty run made by RefreshDatabase

        $creatine = Category::create(['name' => 'Creatine']);
        $whey = Category::create(['name' => 'Whey Protein']);
        $vitamins = Category::create(['name' => 'Vitamins']);
        // Created first: ids after 171 (172, 173) are real BioTechUSA products in BRAND_BY_ID
        $gold = Product::factory()->create(['name' => 'Gold Standard 100% whey 907g', 'category_id' => $whey->id]);
        $vit = Product::factory()->create(['name' => 'Unknown Vitamin', 'category_id' => $vitamins->id]);
        $mono = Product::factory()->create(['name' => '100% CREATINE Monohydrate (88 SERVINGS)', 'category_id' => $creatine->id]);
        DB::table('products')->where('id', $mono->id)->update(['id' => 171]);
        $before = [
            'categories' => DB::table('categories')->orderBy('id')->get()->map(fn ($r) => (array) $r)->all(),
            'products' => DB::table('products')->orderBy('id')->get(['id', 'category_id', 'brand_id'])->map(fn ($r) => (array) $r)->all(),
        ];

        $migration->up();

        $protein = Category::where('name', 'Protein')->firstOrFail();
        $health = Category::where('name', 'Health & Wellness')->firstOrFail();
        $monohydrate = Category::where('name', 'Creatine Monohydrate')->firstOrFail();
        $this->assertSame($protein->id, $whey->fresh()->parent_id);
        $this->assertSame('protein', $protein->icon);
        $this->assertSame($health->id, $vitamins->fresh()->parent_id);
        $this->assertSame($creatine->id, $monohydrate->parent_id);
        $this->assertSame($monohydrate->id, Product::find(171)->category_id, 'creatine 171 moved into Monohydrate');
        $this->assertSame('BioTechUSA', Product::find(171)->brand->name, 'brand by id (the name does not say it)');
        $this->assertSame('Optimum Nutrition', $gold->fresh()->brand->name, 'brand from the name');
        $this->assertNull($vit->fresh()->brand_id);
        $this->assertFalse(Category::where('name', 'Accessories')->exists(), 'main categories with nothing inside are not created');
        $logo = Brand::where('name', 'Optimum Nutrition')->value('logo');
        Storage::disk('public')->assertExists(substr($logo, strlen('storage/')));

        $migration->down();

        $this->assertSame($before['categories'], DB::table('categories')->orderBy('id')->get()->map(fn ($r) => (array) $r)->all());
        $this->assertSame($before['products'], DB::table('products')->orderBy('id')->get(['id', 'category_id', 'brand_id'])->map(fn ($r) => (array) $r)->all());
        $this->assertSame(0, Brand::count());
        Storage::disk('public')->assertMissing(substr($logo, strlen('storage/')));

        $migration->up();   // leaves the schema as RefreshDatabase expects
    }
}

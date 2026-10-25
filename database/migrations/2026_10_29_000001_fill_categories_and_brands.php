<?php

use App\Support\Slugs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

// Fills the new catalogue structure so the admin has nothing to enter:
//  - main categories (Protein, Health & Wellness...) with the existing categories placed inside them,
//    plus new subcategories (Creatine Monohydrate, Glutamine...) that some products move into;
//  - an icon and an order for every category;
//  - brands with their logos (copied from database/data/brand-logos), linked to the products.
// Existing categories are found by name and products by id + the category they are in now, so anything
// the admin changed in the meantime is left alone. Every change is written to catalog_setup_log and
// down() puts each value back as it was.
return new class extends Migration
{
    // Main category => [icon, subcategories as name => icon]. A main category or subcategory that
    // doesn't exist yet is created (only when it ends up with products or subcategories).
    private const TREE = [
        'Protein' => ['protein', [
            'Whey Protein' => 'whey', 'Whey Protein Isolate' => 'isolate', 'Casein' => 'casein',
            'Beef Protein' => 'beef', 'Vegan protein' => 'vegan',
        ]],
        'Creatine' => ['creatine', ['Creatine Monohydrate' => 'crystal', 'Creatine Blends' => 'layers']],
        'Mass Gainers' => ['gainer', []],
        'Pre-Workout' => ['bolt', [
            'Stimulant Pre-Workout' => 'rocket', 'Pump & Stim-Free' => 'pump', 'Caffeine' => 'coffee', 'Beta-Alanine' => 'waves',
        ]],
        'Amino Acids' => ['atom', ['EAA & BCAA' => 'blend', 'Glutamine' => 'shield', 'Citrulline & Arginine' => 'droplet']],
        'Fat Burner' => ['flame', ['Thermogenic' => 'thermo', 'L-Carnitine' => 'battery', 'CLA' => 'tube']],
        'Health & Wellness' => ['heart', [
            'Vitamins' => 'pill', 'Minerals' => 'flask', 'Omega 3' => 'fish', 'Joint Support' => 'bone',
            'Herbs' => 'leaf', 'Collagen' => 'sparkles',
        ]],
        'Testosterone Boosters' => ['testosterone', []],
        'Snacks & Carbs' => ['cookie', ['Protein Bars' => 'bar', 'Carbs' => 'wheat']],
        'Accessories' => ['dumbbell', ['Shakers' => 'shaker', 'Gym Gear' => 'weight', 'Shirts' => 'shirt']],
        'Offers' => ['offer', []],
    ];

    // Subcategory => [the category the products are in now, product ids]
    private const MOVES = [
        'Creatine Monohydrate' => ['Creatine', [171, 449, 32, 298, 34, 213, 392, 429, 230, 35, 473, 195, 430]],
        'Creatine Blends' => ['Creatine', [424, 75]],
        'Stimulant Pre-Workout' => ['Pre-Workout', [52, 51, 265, 421, 484, 45, 464]],
        'Pump & Stim-Free' => ['Pre-Workout', [440, 470, 474, 199, 395, 393]],
        'Caffeine' => ['Pre-Workout', [204, 441, 442, 413]],
        'Beta-Alanine' => ['Pre-Workout', [425, 433]],
        'EAA & BCAA' => ['Amino Acids', [476]],
        'Glutamine' => ['Amino Acids', [183, 135, 445, 446, 266, 478, 436, 196, 332, 431]],
        'Citrulline & Arginine' => ['Amino Acids', [206, 208, 245, 235, 112, 432]],
        'Thermogenic' => ['Fat Burner', [102, 215, 314, 415, 270, 103, 105, 475]],
        'L-Carnitine' => ['Fat Burner', [313, 394, 133, 269]],
        'CLA' => ['Fat Burner', [414, 134, 108, 233, 194]],
        'Minerals' => ['Vitamins', [410, 411, 409, 426, 333, 461, 416, 404, 469, 405, 209, 243]],
        'Omega 3' => ['Vitamins', [412, 427, 401, 400]],
        'Joint Support' => ['Vitamins', [272, 258, 480]],
        'Collagen' => ['Vitamins', [242]],
    ];

    // Brand => [logo file in database/data/brand-logos (or null), words in the product name]
    private const BRANDS = [
        'BioTechUSA' => ['biotechusa', ['biotech', 'iso whey zero', 'pure whey', 'protein power', 'carbox', 'eaa zero', 'one - a - day', 'zinc duo', 'caffeine + taurine']],
        'Optimum Nutrition' => ['optimum-nutrition', ['optimum nutrition', 'gold standard', 'serious mass']],
        'MuscleTech' => ['muscletech', ['muscletech', 'hydroxycut']],
        'Challenger Nutrition' => ['challenger-nutrition', ['challenger', 'thunder gain']],
        'BPI Sports' => ['bpi-sports', ['bpi ']],
        'Blade Sport' => ['blade-sport', ['blade sport']],
        'Nutrend' => ['nutrend', ['nutrend']],
        'NutriTech' => ['nutritech', ['nutritech', 'nutritecch']],
        'Weider' => ['weider', ['weider', 'russian bear']],
        'Dymatize' => ['dymatize', ['dymatize', 'iso 100', 'iso100']],
        'Larry Wheels' => ['larry-wheels', ['larry wheels']],
        'Core Champs' => ['core-champs', ['core champs']],
        'Giant Sports' => ['giant-sports', ['giant sports']],
        'Inner Armour' => ['inner-armour', ['inner armour', 'anabolic peak']],
        'Nutrex Research' => ['nutrex-research', ['nutrex', 'lipo 6', 'lipo6', 'anabol hardcore']],
        'Big Ramy Labs' => ['big-ramy', ['red rex']],
        'Universal Nutrition' => ['universal-nutrition', ['universal', 'animal ']],
        'Victor Martinez' => ['victor-martinez', ['victor martinez']],
        'Six Star' => ['six-star', ['sixstar', 'six star']],
        'Xtend' => ['xtend', ['xtend']],
        'Cellucor' => ['cellucor', ['cellucor', 'c4 original']],
        'RedCon1' => ['redcon1', ['total war', 'redcon1']],
        '21st Century' => ['21st-century', ['21st century']],
        'NovaDiet' => ['novadiet', ['novadiet']],
        'Swanson' => ['swanson', ['swanson']],
        'Metabolic Nutrition' => ['metabolic-nutrition', ['metabolic']],
        'Olimp' => ['olimp', []],
        'Pro Supps' => ['pro-supps', ['prosupps', 'pro supps']],
        'Myprotein' => ['myprotein', ['myprotine', 'myprotein']],
        'Supplement Station' => ['supplement-station', ['supplement station']],
    ];

    // Products whose name doesn't say the brand (checked against their descriptions)
    private const BRAND_BY_ID = [
        115 => 'BioTechUSA', 171 => 'BioTechUSA', 172 => 'BioTechUSA', 173 => 'BioTechUSA', 178 => 'BioTechUSA',
        183 => 'BioTechUSA', 201 => 'Larry Wheels', 215 => 'BPI Sports', 231 => 'Challenger Nutrition',
        242 => 'Olimp', 243 => 'Olimp', 318 => 'Weider', 182 => 'Nutrex Research',
    ];

    private const LOGO_SOURCE = 'database/data/brand-logos';
    private const LOGO_DIRECTORY = 'brands';

    public function up(): void
    {
        Schema::create('catalog_setup_log', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 30);
            $table->unsignedBigInteger('row_id');
            $table->string('old_value')->nullable();
        });

        DB::transaction(function () {
            $this->buildCategories();
            $this->fillBrands();
        });

        Cache::forget('store.categories');
        Cache::forget('store.brands');
    }

    private function buildCategories(): void
    {
        $byName = fn (string $name) => DB::table('categories')->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
        $mainOrder = 0;

        foreach (self::TREE as $mainName => [$mainIcon, $subcategories]) {
            $main = $byName($mainName);
            $mainOrder++;

            // Which subcategories exist, or will exist because products move into them
            $present = [];
            foreach ($subcategories as $subName => $subIcon) {
                $existing = $byName($subName);
                if ($existing || $this->productsToMove($subName)) {
                    $present[$subName] = [$existing, $subIcon];
                }
            }
            if (!$main && !$present) {
                continue;
            }
            if (!$main) {
                $main = $this->createCategory($mainName, null, $mainIcon, $mainOrder);
            } else {
                $this->updateCategory($main, ['parent_id' => null, 'icon' => $mainIcon, 'sort_order' => $mainOrder]);
            }

            $subOrder = 0;
            foreach ($present as $subName => [$existing, $subIcon]) {
                $subOrder++;
                if ($existing && $existing->id === $main->id) {
                    continue;
                }
                if (!$existing) {
                    $existing = $this->createCategory($subName, $main->id, $subIcon, $subOrder);
                } else {
                    // Only a category without subcategories of its own can go inside another one
                    if (DB::table('categories')->where('parent_id', $existing->id)->exists()) {
                        continue;
                    }
                    $this->updateCategory($existing, ['parent_id' => $main->id, 'icon' => $subIcon, 'sort_order' => $subOrder]);
                }
                $this->moveProducts($subName, $existing->id);
            }
        }
    }

    // Ids of the products that go into this subcategory: still in the category they were in when this was written
    private function productsToMove(string $subName): array
    {
        if (!isset(self::MOVES[$subName])) {
            return [];
        }
        [$fromName, $ids] = self::MOVES[$subName];
        $from = DB::table('categories')->whereRaw('LOWER(name) = ?', [mb_strtolower($fromName)])->value('id');

        return $from ? DB::table('products')->whereIn('id', $ids)->where('category_id', $from)->pluck('id')->all() : [];
    }

    private function moveProducts(string $subName, int $categoryId): void
    {
        foreach (DB::table('products')->whereIn('id', $this->productsToMove($subName))->get(['id', 'category_id']) as $product) {
            $this->log('product_category', $product->id, $product->category_id);
            DB::table('products')->where('id', $product->id)->update(['category_id' => $categoryId]);
        }
    }

    private function createCategory(string $name, ?int $parentId, string $icon, int $order): object
    {
        $now = now();
        $id = DB::table('categories')->insertGetId([
            'name' => $name,
            'slug' => Slugs::unique('categories', $name, null, 'category'),
            'parent_id' => $parentId,
            'icon' => $icon,
            'sort_order' => $order,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->log('category_created', $id, null);

        return DB::table('categories')->find($id);
    }

    // The new columns start empty (null, 0); down() empties them again
    private function updateCategory(object $category, array $values): void
    {
        $this->log('category_updated', $category->id, null);
        DB::table('categories')->where('id', $category->id)->update($values);
    }

    private function fillBrands(): void
    {
        $products = DB::table('products')->whereNull('brand_id')->get(['id', 'name']);
        $assigned = [];

        foreach ($products as $product) {
            $brand = self::BRAND_BY_ID[$product->id] ?? $this->brandFromName($product->name);
            if ($brand) {
                $assigned[$brand][] = $product->id;
            }
        }

        $order = 0;
        foreach (self::BRANDS as $name => [$logo]) {
            $order++;
            if (empty($assigned[$name])) {
                continue;
            }

            $brandId = DB::table('brands')->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->value('id');
            if (!$brandId) {
                $brandId = DB::table('brands')->insertGetId([
                    'name' => $name,
                    'slug' => Slugs::unique('brands', $name, null, 'brand'),
                    'logo' => $this->copyLogo($logo),
                    'sort_order' => $order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->log('brand_created', $brandId, null);
            }

            foreach ($assigned[$name] as $productId) {
                $this->log('product_brand', $productId, null);
            }
            DB::table('products')->whereIn('id', $assigned[$name])->update(['brand_id' => $brandId]);
        }
    }

    private function brandFromName(string $name): ?string
    {
        $name = ' ' . mb_strtolower($name) . ' ';
        foreach (self::BRANDS as $brand => [, $words]) {
            foreach ($words as $word) {
                if (str_contains($name, $word)) {
                    return $brand;
                }
            }
        }

        return null;
    }

    // Copies the logo onto the public disk (where the admin's uploads go) and returns the stored path
    private function copyLogo(?string $file): ?string
    {
        $source = $file ? base_path(self::LOGO_SOURCE . "/$file.webp") : null;
        if (!$source || !is_file($source)) {
            return null;
        }
        $path = self::LOGO_DIRECTORY . "/$file.webp";
        Storage::disk('public')->put($path, file_get_contents($source));

        return 'storage/' . $path;
    }

    private function log(string $kind, int $rowId, $oldValue): void
    {
        DB::table('catalog_setup_log')->insert(['kind' => $kind, 'row_id' => $rowId, 'old_value' => $oldValue]);
    }

    public function down(): void
    {
        $log = DB::table('catalog_setup_log')->orderByDesc('id')->get()->groupBy('kind');

        DB::transaction(function () use ($log) {
            foreach ($log['product_category'] ?? [] as $entry) {
                DB::table('products')->where('id', $entry->row_id)->update(['category_id' => $entry->old_value]);
            }
            foreach ($log['product_brand'] ?? [] as $entry) {
                DB::table('products')->where('id', $entry->row_id)->update(['brand_id' => null]);
            }
            foreach ($log['category_updated'] ?? [] as $entry) {
                DB::table('categories')->where('id', $entry->row_id)->update(['parent_id' => null, 'icon' => null, 'sort_order' => 0]);
            }

            $created = collect($log['category_created'] ?? [])->pluck('row_id');
            // Something the admin added inside a new category since then moves out instead of being lost
            DB::table('categories')->whereIn('parent_id', $created)->whereNotIn('id', $created)->update(['parent_id' => null]);
            DB::table('categories')->whereIn('id', $created)->update(['parent_id' => null]);
            DB::table('categories')->whereIn('id', $created)
                ->whereNotExists(fn ($q) => $q->from('products')->whereColumn('products.category_id', 'categories.id'))
                ->delete();

            foreach ($log['brand_created'] ?? [] as $entry) {
                $logo = DB::table('brands')->where('id', $entry->row_id)->value('logo');
                if ($logo && str_starts_with($logo, 'storage/' . self::LOGO_DIRECTORY . '/')) {
                    Storage::disk('public')->delete(substr($logo, strlen('storage/')));
                }
                DB::table('products')->where('brand_id', $entry->row_id)->update(['brand_id' => null]);
                DB::table('brands')->where('id', $entry->row_id)->delete();
            }
        });

        Schema::dropIfExists('catalog_setup_log');
        Cache::forget('store.categories');
        Cache::forget('store.brands');
    }
};

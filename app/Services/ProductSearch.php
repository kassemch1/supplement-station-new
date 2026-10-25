<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// The store search: finds products by name, brand and category, forgiving typos ("protien", "creatin"),
// missing or extra spaces ("preworkout", "iso 100") and plurals. The catalogue is small (a few hundred
// products), so every product is scored in PHP from a cached index instead of with SQL LIKE.
class ProductSearch
{
    // A query word counts for this much when it is the whole word, the start of a word, inside a word,
    // or a word with a typo in it (minus a little per wrong letter)
    private const EXACT = 1.0;
    private const PREFIX = 0.85;
    private const INSIDE = 0.6;
    private const TYPO = 0.7;
    private const TYPO_STEP = 0.15;

    // Where the word was found
    private const NAME = 1.0;
    private const BRAND = 0.9;
    private const CATEGORY = 0.8;

    // Results scoring under this share of the best one are left out
    private const KEEP_SHARE = 0.45;

    /** @var array<string, int[]> ids per catalogue version and query (the shop asks twice: filter and sort) */
    private static array $memo = [];

    /** @return int[] ids of the matching products, best match first */
    public static function ids(string $query): array
    {
        $key = self::indexKey() . '|' . self::normalize($query);

        return self::$memo[$key] ??= array_keys(self::scores($query));
    }

    /** @return array<int, float> [product id => score], best first */
    public static function scores(string $query): array
    {
        $words = self::words($query);
        if (!$words) {
            return [];
        }
        // Short words and lone letters ("c" of "c4") only add to the score; the others must all match
        $required = array_values(array_filter($words, fn ($w) => mb_strlen($w) >= 2 || ctype_digit($w)));
        $required = $required ?: $words;
        $compactQuery = implode('', $words);

        $best = array_flip(Catalog::bestSellerIds(60));
        $queryText = implode(' ', $words);

        $scores = [];
        foreach (self::index() as $product) {
            $fields = [
                [$product['name_words'], self::NAME],
                [$product['brand_words'], self::BRAND],
                [$product['category_words'], self::CATEGORY],
            ];
            // The words run together or apart differently ("preworkout" / "pre-workout", "iso100")
            $phrase = strlen($compactQuery) >= 4
                && (str_contains($product['compact'], $compactQuery) || str_contains($product['category_compact'], $compactQuery));

            $total = 0.0;
            $missing = false;
            foreach ($words as $word) {
                $wordScore = 0.0;
                foreach ($fields as [$tokens, $weight]) {
                    foreach ($tokens as $token) {
                        $wordScore = max($wordScore, self::wordScore($word, $token) * $weight);
                    }
                }
                if ($wordScore === 0.0 && in_array($word, $required, true)) {
                    $missing = true;
                }
                $total += $wordScore;
            }
            if ($missing && !$phrase) {
                continue;
            }

            $total += $phrase ? 1.5 : 0;
            $total += str_starts_with($product['name_text'], $queryText) ? 0.5 : 0;
            $total += $product['stock'] ? 0.3 : 0;
            $total += isset($best[$product['id']]) ? 0.2 * (1 - $best[$product['id']] / 60) : 0;
            $scores[$product['id']] = round($total, 4);
        }

        // Weak typo matches next to strong ones are noise ("nitrotech" is not NutriTech creatine)
        $top = $scores ? max($scores) : 0;
        $scores = array_filter($scores, fn ($score) => $score >= self::KEEP_SHARE * $top);

        // Best score first; equal scores keep the newest product first
        uksort($scores, fn ($a, $b) => [$scores[$b], $b] <=> [$scores[$a], $a]);

        return $scores;
    }

    /**
     * What the search box shows while typing: a few products, brands and categories.
     *
     * @return array{products: Collection, brands: Collection, categories: Collection}
     */
    public static function suggest(string $query, int $limit = 6): array
    {
        $ids = array_slice(self::ids($query), 0, $limit);
        $products = Product::with('images', 'brand')->whereIn('id', $ids ?: [0])->get()
            ->sortBy(fn (Product $p) => array_search($p->id, $ids, true))
            ->map(fn (Product $p) => Catalog::card($p))
            ->values();

        $words = self::words($query);
        $matchesName = function (string $name) use ($words): float {
            $tokens = self::words($name);
            $sum = 0.0;
            foreach ($words as $word) {
                $best = 0.0;
                foreach ($tokens as $token) {
                    $best = max($best, self::wordScore($word, $token));
                }
                if ($best === 0.0) {
                    return 0.0;
                }
                $sum += $best;
            }

            return $sum;
        };

        $brands = Brand::cachedAll()
            ->map(fn (Brand $b) => ['brand' => $b, 'score' => $matchesName($b->name)])
            ->filter(fn ($row) => $row['score'] > 0)->sortByDesc('score')->take(3)
            ->map(fn ($row) => ['name' => $row['brand']->name, 'url' => route('shop', ['brand' => $row['brand']->slug])])
            ->values();

        $counts = Catalog::categoryCounts();
        $categories = Category::cachedAll()
            ->filter(fn (Category $c) => ($counts[$c->id] ?? 0) > 0)
            ->map(fn (Category $c) => ['category' => $c, 'score' => $matchesName($c->name)])
            ->filter(fn ($row) => $row['score'] > 0)->sortByDesc('score')->take(3)
            ->map(fn ($row) => ['name' => $row['category']->name, 'url' => $row['category']->url()])
            ->values();

        return ['products' => $products, 'brands' => $brands, 'categories' => $categories];
    }

    // How well one query word matches one word of a product (0 = not at all)
    private static function wordScore(string $word, string $token): float
    {
        if ($word === $token) {
            return self::EXACT;
        }
        $length = strlen($word);
        if (str_starts_with($token, $word)) {
            return self::PREFIX;
        }
        if ($length >= 3 && str_contains($token, $word)) {
            return self::INSIDE;
        }
        // Typos: one wrong letter in a word of 4-7 letters, two from 8 letters. Numbers must match exactly
        // (a 2kg tub is not a 5kg tub)
        if ($length < 4 || ctype_digit($word) || ctype_digit($token)) {
            return 0.0;
        }
        $allowed = $length >= 8 ? 2 : 1;
        $start = substr($token, 0, $length);
        // A typo in both of the first two letters is a different word
        if (strlen($token) < 3 || abs(strlen($token) - $length) > $allowed + 3 || ($word[0] !== $token[0] && $word[1] !== $token[1])) {
            return 0.0;
        }
        // Against the whole word, and against its start ("protei" while typing "protein")
        $distance = min(levenshtein($word, $token), levenshtein($word, $start));
        // Swapped letters count once; worth the slower check only when it can bring the word within reach
        if ($distance > $allowed && $distance <= 2 * $allowed) {
            $distance = min(self::distance($word, $token), self::distance($word, $start));
        }
        if ($distance > $allowed) {
            return 0.0;
        }

        return self::TYPO - self::TYPO_STEP * $distance;
    }

    // Letters to add, remove, change or swap with the next one to turn $a into $b ("protien" -> "protein"
    // is one swap, where plain levenshtein() counts two changes)
    private static function distance(string $a, string $b): int
    {
        $n = strlen($a);
        $m = strlen($b);
        $d = [];
        for ($i = 0; $i <= $n; $i++) {
            $d[$i][0] = $i;
        }
        for ($j = 0; $j <= $m; $j++) {
            $d[0][$j] = $j;
        }
        for ($i = 1; $i <= $n; $i++) {
            for ($j = 1; $j <= $m; $j++) {
                $cost = $a[$i - 1] === $b[$j - 1] ? 0 : 1;
                $d[$i][$j] = min($d[$i - 1][$j] + 1, $d[$i][$j - 1] + 1, $d[$i - 1][$j - 1] + $cost);
                if ($i > 1 && $j > 1 && $a[$i - 1] === $b[$j - 2] && $a[$i - 2] === $b[$j - 1]) {
                    $d[$i][$j] = min($d[$i][$j], $d[$i - 2][$j - 2] + 1);
                }
            }
        }

        return $d[$n][$m];
    }

    /** @return string[] the words of a text, lower case, without accents or punctuation, numbers apart */
    public static function words(string $text): array
    {
        $text = self::normalize($text);

        return $text === '' ? [] : explode(' ', $text);
    }

    private static function normalize(string $text): string
    {
        $text = mb_strtolower(Str::ascii($text));
        $text = str_replace('&', ' and ', $text);
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);
        // "2kg" -> "2 kg", "iso100" -> "iso 100", so both ways of writing them match
        $text = preg_replace('/(?<=\d)(?=[a-z])|(?<=[a-z])(?=\d)/', ' ', $text);

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    // Changes whenever a product is added, changed or archived, or a brand or category is renamed or
    // moved, so the index is rebuilt
    private static function indexKey(): string
    {
        $version = DB::table('products')->whereNull('deleted_at')
            ->selectRaw('COUNT(*) AS n, MAX(updated_at) AS changed, MAX(id) AS last')->first();
        $names = [
            Brand::cachedAll()->map(fn (Brand $b) => [$b->id, $b->name])->all(),
            Category::cachedAll()->map(fn (Category $c) => [$c->id, $c->name, $c->parent_id])->all(),
        ];

        return 'store.search_index.' . md5(json_encode([$version, $names]));
    }

    // Every product's words, cleaned up once: name, brand, category (with its main category)
    private static function index(): array
    {
        return Cache::remember(self::indexKey(), 86400, function () {
            $brands = Brand::cachedAll()->keyBy('id');
            $categories = Category::cachedAll()->keyBy('id');

            return Product::query()->get(['id', 'name', 'brand_id', 'category_id', 'stock'])
                ->map(function (Product $p) use ($brands, $categories) {
                    $brand = $brands[$p->brand_id] ?? null;
                    $category = $categories[$p->category_id] ?? null;
                    $parent = $category && $category->parent_id ? ($categories[$category->parent_id] ?? null) : null;
                    $nameWords = self::words((string) $p->name);
                    $brandWords = $brand ? self::words($brand->name) : [];
                    $categoryWords = array_merge($parent ? self::words($parent->name) : [], $category ? self::words($category->name) : []);

                    return [
                        'id' => (int) $p->id,
                        'stock' => (bool) $p->stock,
                        'name_words' => $nameWords,
                        'brand_words' => $brandWords,
                        'category_words' => $categoryWords,
                        'name_text' => implode(' ', $nameWords),
                        'compact' => implode('', array_merge($brandWords, $nameWords)),
                        'category_compact' => implode('', $categoryWords),
                    ];
                })
                ->all();
        });
    }
}

<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

// Text and structured data (schema.org JSON-LD) that search engines read from the store pages.
// Contact details match the ones shown in the footer.
class Seo
{
    public const NAME = 'Supplement Station';

    // One line of plain text for a meta description: no tags, no line breaks, cut at a word
    public static function text(?string $text, int $limit = 155): string
    {
        $plain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        return Str::limit($plain, $limit, '…', preserveWords: true);
    }

    // The <script type="application/ld+json"> tag for a page, from items made by the methods below.
    // (Built here, not in the views: Blade would read the "@context" key as its @context directive.)
    // JSON_HEX_TAG keeps a "</script>" typed in a product name or review harmless.
    public static function script(array $items): HtmlString
    {
        $json = json_encode(
            ['@context' => 'https://schema.org', '@graph' => array_values(array_filter($items))],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
        );

        return new HtmlString('<script type="application/ld+json">' . $json . '</script>');
    }

    public static function faq($faqs): ?array
    {
        if ($faqs->isEmpty()) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => self::text($faq->question, 300),
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => self::text($faq->answer, 2000)],
            ])->values()->all(),
        ];
    }

    // A shop or category page and the products listed on it
    public static function collection(string $name, string $url, $products, int $firstPosition): array
    {
        return [
            '@type' => 'CollectionPage',
            'name' => $name,
            'url' => $url,
            'isPartOf' => ['@id' => url('/') . '/#website'],
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => count($products),
                'itemListElement' => collect($products)->values()->map(fn ($product, $i) => [
                    '@type' => 'ListItem',
                    'position' => $firstPosition + $i,
                    'url' => $product->url(),
                    'name' => $product->name,
                ])->all(),
            ],
        ];
    }

    public static function store(): array
    {
        return [
            '@type' => 'Store',
            '@id' => url('/') . '/#store',
            'name' => self::NAME,
            'url' => url('/') . '/',
            'logo' => asset('assets/img/logo/preloader2.png'),
            'image' => asset('assets/img/logo/preloader2.png'),
            'description' => 'Supplement store in Beirut, Lebanon: protein, creatine, mass gainers, pre-workouts, vitamins and gym gear.',
            'telephone' => config('store.phone'),
            'email' => 'admin@supplement-station.com',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Msharafieh, facing Garden State',
                'addressLocality' => 'Beirut',
                'addressCountry' => 'LB',
            ],
            'areaServed' => ['@type' => 'Country', 'name' => 'Lebanon'],
            'openingHoursSpecification' => [[
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'opens' => '09:00',
                'closes' => '21:00',
            ]],
            'contactPoint' => [[
                '@type' => 'ContactPoint',
                'telephone' => config('store.phone'),
                'contactType' => 'customer service',
                'areaServed' => 'LB',
                'availableLanguage' => ['English', 'Arabic'],
            ]],
            'sameAs' => ['https://www.instagram.com/supplement_station_lb/'],
        ];
    }

    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => url('/') . '/#website',
            'name' => self::NAME,
            'url' => url('/') . '/',
            'publisher' => ['@id' => url('/') . '/#store'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => route('shop') . '?shop-search={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    // [[name, url], ...] → BreadcrumbList; the last item is the page itself
    public static function breadcrumbs(array $items): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn ($item, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item[0],
                'item' => $item[1],
            ])->all(),
        ];
    }

    // The product page: price after discount, stock, photos, reviews
    public static function product(Product $product, ?string $categoryName, $reviews): array
    {
        $images = $product->images->map(fn ($image) => asset($image->url))->values()->all();
        $price = number_format((float) $product->price_after_discount, 2, '.', '');

        $data = [
            '@type' => 'Product',
            '@id' => $product->url() . '#product',
            'name' => $product->name,
            'url' => $product->url(),
            'sku' => (string) $product->id,
            'description' => self::text($product->description ?: $product->name, 5000),
            'offers' => [
                '@type' => 'Offer',
                'url' => $product->url(),
                'price' => $price,
                'priceCurrency' => 'USD',
                // Google wants an end date for a discounted price; the admin can change it at any time
                'priceValidUntil' => now()->addMonth()->endOfMonth()->toDateString(),
                'availability' => $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller' => ['@id' => url('/') . '/#store'],
            ],
        ];

        if ($images) {
            $data['image'] = $images;
        }
        if ($categoryName) {
            $data['category'] = $categoryName;
        }
        if ($product->brand) {
            $data['brand'] = ['@type' => 'Brand', 'name' => $product->brand->name];
        }

        if ($reviews->count() > 0) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round((float) $reviews->avg('rating'), 1),
                'reviewCount' => $reviews->count(),
                'bestRating' => 5,
                'worstRating' => 1,
            ];
            $data['review'] = $reviews->sortByDesc('created_at')->take(10)->map(fn ($review) => array_filter([
                '@type' => 'Review',
                'author' => ['@type' => 'Person', 'name' => Str::limit(trim($review->name) ?: 'Customer', 60, '')],
                'datePublished' => $review->created_at?->toDateString(),
                'reviewBody' => self::text($review->message, 1000),
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => (int) $review->rating, 'bestRating' => 5, 'worstRating' => 1],
            ]))->values()->all();
        }

        return $data;
    }
}

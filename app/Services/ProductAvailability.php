<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

// Checks that a product, with the options the customer picked, can be bought right now.
// Options are matched to the product's own option rows by option name AND value, so a value that
// belongs to another option type, another product, or nothing at all is rejected.
class ProductAvailability
{
    /**
     * @return array{error: ?string, productOptions: Collection}
     */
    public static function check(?Product $product, mixed $selectedOptions): array
    {
        $fail = fn (string $message) => ['error' => $message, 'productOptions' => collect()];

        if (!$product) {
            return $fail('This product is no longer available.');
        }
        if (!$product->stock) {
            return $fail("{$product->name} is sold out.");
        }
        if (!is_array($selectedOptions)) {
            return $fail('The selected options are not valid.');
        }

        // Callers checking many lines eager-load productOptions.option, so this runs no query
        $groups = $product->loadMissing('productOptions.option')->productOptions
            ->filter(fn ($productOption) => $productOption->option)
            ->groupBy(fn ($productOption) => $productOption->option->option_name);

        foreach ($groups->keys() as $optionName) {
            if (!array_key_exists($optionName, $selectedOptions)) {
                return $fail("Please choose a {$optionName}.");
            }
        }

        $matched = collect();
        foreach ($selectedOptions as $optionName => $value) {
            $productOption = ($groups[$optionName] ?? collect())
                ->first(fn ($productOption) => (string) $productOption->option_value === (string) $value);

            if (!$productOption) {
                return $fail("{$optionName} \"{$value}\" is not available for {$product->name}.");
            }
            if (!$productOption->stock) {
                return $fail("{$product->name} ({$optionName}: {$value}) is sold out.");
            }
            $matched->push($productOption);
        }

        return ['error' => null, 'productOptions' => $matched];
    }
}

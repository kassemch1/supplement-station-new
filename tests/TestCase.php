<?php

namespace Tests;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    // RefreshDatabase wipes the database. The suite runs on an in-memory SQLite database
    // (phpunit.xml); never let it reach a real one (the local copy of the live data, say).
    protected function setUpTraits()
    {
        if (isset(class_uses_recursive(static::class)[RefreshDatabase::class])) {
            $this->assertTestDatabase();
        }

        return parent::setUpTraits();
    }

    private function assertTestDatabase(): void
    {
        $connection = config('database.default');
        if (config("database.connections.$connection.driver") !== 'sqlite'
            || config("database.connections.$connection.database") !== ':memory:') {
            throw new \RuntimeException('Tests that reset the database only run on SQLite :memory: (see phpunit.xml).');
        }
    }

    // A product page address (/products/{slug}); /products/{id} only redirects there
    protected function productUrl($id): string
    {
        return Product::withTrashed()->findOrFail($id)->url();
    }

    protected function admin(): static
    {
        return $this->actingAs(User::factory()->create());
    }

    protected function addToCart(Product|int $product, array $options = [], int $quantity = 1): TestResponse
    {
        return $this->postJson('/cart/add', [
            'product_id' => $product instanceof Product ? $product->id : $product,
            'quantity' => $quantity,
            'selected_options' => json_encode((object) $options),
        ]);
    }

    protected function placeOrder(array $extra = []): TestResponse
    {
        return $this->postJson('/place-order', array_merge([
            'billing_first_name' => 'Test Customer',
            'billing_phone' => '03123456',
            'billing_address_1' => 'Hamra Street',
            'billing_city' => 'Beirut',
            'billing_email' => 'customer@example.com',
            'checkout_token' => (string) Str::uuid(),
        ], $extra));
    }
}

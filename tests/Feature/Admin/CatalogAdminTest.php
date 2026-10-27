<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\Option;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\Review;
use App\Models\SpecialOffer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Categories, options, FAQs, plans, special offers, reviews and coupons, as the admin pages send them
class CatalogAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin();
    }

    public function test_categories(): void
    {
        $this->postJson('/manageCategories/store', ['name' => 'Vitamins'])->assertOk()->assertJson(['status' => 'success']);
        $this->postJson('/manageCategories/store', ['name' => 'Vitamins'])->assertOk()->assertJson(['status' => 'exists']);
        $category = Category::sole();
        $this->assertSame('vitamins', $category->slug);

        $this->putJson('/manageCategories/update', ['category_id' => $category->id, 'name' => 'Vitamins & Minerals'])->assertJson(['status' => 'success']);
        $this->assertSame('Vitamins & Minerals', $category->fresh()->name);
        $this->postJson('/manageCategories/store', ['name' => ''])->assertJsonValidationErrors('name');
        $this->deleteJson('/manageCategories/destroy', ['id' => $category->id])->assertOk();
        $this->assertSame(0, Category::count());
    }

    public function test_options_and_product_options(): void
    {
        $product = Product::factory()->create();

        $this->postJson('/manageOptions/store', ['option_name' => 'Flavor'])->assertOk();
        $option = Option::sole();
        $this->putJson('/manageOptions/update', ['option_id' => $option->id, 'option_name' => 'Taste'])->assertOk();
        $this->assertSame('Taste', $option->fresh()->option_name);

        $this->postJson('/manageProductsOptions/store', ['product_id' => $product->id, 'option_id' => $option->id, 'option_value' => 'Vanilla', 'stock' => 1])->assertOk();
        $this->postJson('/manageProductsOptions/store', ['product_id' => 999, 'option_id' => $option->id, 'option_value' => 'X'])->assertJsonValidationErrors('product_id');
        $value = ProductOption::sole();

        // The product a value belongs to can't be changed through the edit form
        $other = Product::factory()->create();
        $this->putJson("/manageProductsOptions/{$value->id}", ['product_id' => $other->id, 'option_id' => $option->id, 'option_value' => 'Mango', 'stock' => 0])->assertOk();
        $value->refresh();
        $this->assertSame(['Mango', 0, $product->id], [$value->option_value, (int) $value->stock, $value->product_id]);
        $this->get("/manageProductsOptions/{$product->id}")->assertOk()->assertSee('Mango');

        $this->deleteJson("/manageProductsOptions/{$value->id}")->assertOk();
        $this->deleteJson('/manageOptions/destroy', ['option_id' => $option->id])->assertOk();
        $this->assertSame(0, ProductOption::count() + Option::count());
    }

    public function test_faqs_show_on_the_home_page_and_plans_do_not(): void
    {
        $this->postJson('/manageFaq/store', ['question' => 'Do you deliver?', 'answer' => 'Everywhere in Lebanon.'])->assertOk();
        $faq = Faq::sole();
        $this->putJson('/manageFaq/update', ['faqId' => $faq->id, 'question' => 'Do you deliver to Tripoli?', 'answer' => 'Yes.'])->assertOk();

        $this->postJson('/managePlan/store', ['type' => 'Monthly', 'price' => 30, 'point1' => 'Coaching'])->assertOk();
        $plan = Plan::sole();
        $this->putJson('/managePlan/update', ['planId' => $plan->id, 'type' => 'Yearly', 'price' => 300, 'point1' => 'Coaching', 'point2' => 'Diet plan'])->assertOk();
        $this->assertEquals(300, $plan->fresh()->price);
        $this->postJson('/managePlan/store', ['type' => '', 'price' => 'abc'])->assertJsonValidationErrors(['type', 'price']);

        // The site sells no plans, so the home page shows the questions but not the plans the admin keeps
        $this->get('/home')->assertOk()->assertSee('Do you deliver to Tripoli?')->assertDontSee('Diet plan');

        $this->deleteJson('/manageFaq/destroy', ['id' => $faq->id])->assertOk();
        $this->deleteJson('/managePlan/destroy', ['id' => $plan->id])->assertOk();
        $this->assertSame(0, Faq::count() + Plan::count());
    }

    public function test_special_offers_are_limited_ordered_and_swapped(): void
    {
        $products = Product::factory()->count(7)->create();
        foreach ($products->take(6) as $product) {
            $this->post('/admin/special-offers', ['product_id' => $product->id])->assertRedirect('/admin/special-offers');
        }
        $this->assertSame([1, 2, 3, 4, 5, 6], SpecialOffer::orderBy('position')->pluck('position')->all());

        $this->post('/admin/special-offers', ['product_id' => $products[6]->id])->assertSessionHasErrors('product_id');
        $this->assertSame(6, SpecialOffer::count());

        $first = SpecialOffer::where('position', 1)->first();
        $last = SpecialOffer::where('position', 6)->first();
        $this->put("/admin/special-offers/{$last->id}", ['position' => 1])->assertRedirect();
        $this->assertSame([6, 1], [$first->fresh()->position, $last->fresh()->position], 'positions swap');

        $this->delete("/admin/special-offers/{$first->id}")->assertRedirect();
        $this->post('/admin/special-offers', ['product_id' => $last->product_id])->assertSessionHasErrors(['product_id' => 'This product is already a special offer.']);
        $this->post('/admin/special-offers', ['product_id' => $first->product_id])->assertSessionHasNoErrors();
        $this->assertSame(6, SpecialOffer::where('product_id', $first->product_id)->value('position'), 'takes the free position');
    }

    public function test_reviews_can_be_removed(): void
    {
        $review = Review::factory()->create(['name' => 'Spammer']);

        $this->get("/manageProductReview/{$review->product_id}")->assertOk()->assertSee('Spammer');
        $this->deleteJson("/manageProductReview/{$review->id}")->assertOk();
        $this->assertModelMissing($review);
    }

    public function test_coupons(): void
    {
        $this->post('/admin/coupons', ['code' => ' newyear ', 'name' => 'New year', 'type' => 'single_use', 'discount_percentage' => 15,
            'minimum_order_amount' => 0, 'max_uses' => 5, 'expires_at' => '2030-01-01T23:59'])->assertRedirect('/admin/coupons');
        $coupon = Coupon::sole();
        $this->assertSame(['NEWYEAR', 1, true], [$coupon->code, $coupon->max_uses, $coupon->is_active], 'single use means one use');
        $this->assertSame('2030-01-01 23:59:00', $coupon->getRawOriginal('expires_at'), 'stored as typed (Beirut time)');
        $this->get("/admin/coupons/{$coupon->id}/edit")->assertSee('2030-01-01T23:59');
        $this->post('/admin/coupons', ['code' => 'FAR', 'name' => 'x', 'type' => 'reusable', 'discount_percentage' => 5, 'minimum_order_amount' => 0, 'expires_at' => '2099-01-01'])
            ->assertSessionHasErrors(['expires_at' => 'The expiry date must be before 2038.']);

        $this->post('/admin/coupons', ['code' => 'NEWYEAR', 'name' => 'x', 'type' => 'reusable', 'discount_percentage' => 5, 'minimum_order_amount' => 0])
            ->assertSessionHasErrors(['code' => 'This coupon code is already in use.']);
        $this->post('/admin/coupons', ['code' => 'PAST', 'name' => 'x', 'type' => 'reusable', 'discount_percentage' => 5, 'minimum_order_amount' => 0, 'expires_at' => '2000-01-01'])
            ->assertSessionHasErrors('expires_at');

        // Editing: no checkbox means switched off; clearing the date removes the expiry
        $this->put("/admin/coupons/{$coupon->id}", ['name' => 'New year 2', 'type' => 'reusable', 'discount_percentage' => 20,
            'minimum_order_amount' => 10, 'max_uses' => 50, 'expires_at' => ''])->assertRedirect('/admin/coupons');
        $coupon->refresh();
        $this->assertSame(['New year 2', 50, false, null], [$coupon->name, $coupon->max_uses, $coupon->is_active, $coupon->expires_at]);

        $this->patch("/admin/coupons/{$coupon->id}/toggle-status")->assertRedirect();
        $this->assertTrue($coupon->fresh()->is_active);
        $this->get('/admin/coupons?status=active&search=new')->assertSee('NEWYEAR');
        $this->get('/admin/coupons?status=inactive')->assertDontSee('NEWYEAR');

        Order::factory()->create(['coupon_id' => $coupon->id]);
        $this->delete("/admin/coupons/{$coupon->id}")->assertSessionHas('error');
        $this->assertModelExists($coupon);

        $unused = Coupon::factory()->create();
        $this->delete("/admin/coupons/{$unused->id}")->assertSessionHas('success');
        $this->assertModelMissing($unused);
    }
}

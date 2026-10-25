<?php

namespace Tests\Feature\Store;

use App\Jobs\SendProductNewsletter;
use App\Mail\ContactMail;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewsletterAndContactTest extends TestCase
{
    use RefreshDatabase;

    // ---------- the pop-up ----------

    public function test_subscribe_once_and_come_back_after_leaving(): void
    {
        $this->postJson('/subscribe', ['email' => 'fan@example.com'])->assertOk()->assertJson(['success' => true]);
        $this->assertTrue(Subscription::where('email', 'fan@example.com')->value('is_active'));
        $this->getJson('/subscription/check-modal')->assertJson(['show_modal' => false]);

        $this->postJson('/subscribe', ['email' => 'fan@example.com'])->assertOk()
            ->assertJson(['success' => false, 'message' => 'This email is already subscribed. Please try a different email address.']);

        Subscription::where('email', 'fan@example.com')->update(['is_active' => false]);
        $this->postJson('/subscribe', ['email' => 'fan@example.com'])->assertOk()->assertJson(['success' => true]);
        $this->assertSame(1, Subscription::count());
        $this->assertTrue(Subscription::sole()->is_active);

        $this->postJson('/subscribe', ['email' => 'nope'])->assertStatus(422);
    }

    public function test_no_thanks_hides_the_pop_up(): void
    {
        $this->getJson('/subscription/check-modal')->assertJson(['show_modal' => true]);
        $this->postJson('/subscription/cancel')->assertOk();
        $this->getJson('/subscription/check-modal')->assertJson(['show_modal' => false]);
    }

    // ---------- sending ----------

    public function test_the_admin_gets_an_answer_at_once_and_emails_go_out_after(): void
    {
        Bus::fake();
        $product = Product::factory()->create();
        Subscription::factory()->count(3)->create();
        Subscription::factory()->inactive()->create();

        $this->admin()->postJson('/admin/products/send-email', ['product_id' => $product->id])->assertOk()
            ->assertJsonFragment(['message' => "Sending \"{$product->name}\" to 3 subscriber(s) now. You can keep working; failures are written to the log."]);

        Bus::assertDispatchedAfterResponse(SendProductNewsletter::class, fn ($job) => $job->productId === $product->id);
    }

    public function test_no_active_subscribers_sends_nothing(): void
    {
        Bus::fake();
        Subscription::factory()->inactive()->create();

        $this->admin()->postJson('/admin/products/send-email', ['product_id' => Product::factory()->create()->id])
            ->assertJson(['success' => false, 'message' => 'No active subscribers found.']);
        Bus::assertNothingDispatched();
    }

    public function test_every_active_subscriber_gets_the_email_with_a_working_unsubscribe(): void
    {
        config(['mail.default' => 'array']);
        $product = Product::factory()->withImage()->create();
        $active = Subscription::factory()->count(2)->create();
        Subscription::factory()->inactive()->create();

        (new SendProductNewsletter($product->id))->handle();

        $messages = collect(app('mail.manager')->mailer('array')->getSymfonyTransport()->messages());
        $this->assertCount(2, $messages);
        foreach ($messages as $sent) {
            $email = $sent->getOriginalMessage();
            $to = $email->getTo()[0]->getAddress();
            $subscription = $active->firstWhere('email', $to);
            $this->assertNotNull($subscription);

            $header = $email->getHeaders()->get('List-Unsubscribe')->getBodyAsString();
            $this->assertSame('<' . $subscription->unsubscribeUrl() . '>', $header);
            $this->assertSame('List-Unsubscribe=One-Click', $email->getHeaders()->get('List-Unsubscribe-Post')->getBodyAsString());
            $this->assertStringContainsString(e($subscription->unsubscribeUrl()), $email->getHtmlBody());
            $this->assertStringContainsString($product->url(), $email->getHtmlBody());
            $this->assertSame(config('mail.admin_address'), $email->getReplyTo()[0]->getAddress());
        }
    }

    // ---------- unsubscribing ----------

    public function test_the_link_asks_first_then_unsubscribes(): void
    {
        $subscription = Subscription::factory()->create(['email' => 'leaver@example.com']);
        $url = $subscription->unsubscribeUrl();

        // Opening the link (or a mail scanner opening it) changes nothing
        $this->get($url)->assertOk()->assertSee('Unsubscribe from our emails?')->assertSee('leaver@example.com');
        $this->assertTrue($subscription->fresh()->is_active);

        // The button posts back to the same signed address, without a CSRF token
        $this->post($url)->assertOk()->assertSee('You are unsubscribed');
        $this->assertFalse($subscription->fresh()->is_active);
        $this->get($url)->assertOk()->assertSee('You are unsubscribed');
    }

    public function test_email_apps_one_click_unsubscribe(): void
    {
        $subscription = Subscription::factory()->create();

        $this->post($subscription->unsubscribeUrl(), ['List-Unsubscribe' => 'One-Click'])->assertNoContent();
        $this->assertFalse($subscription->fresh()->is_active);
    }

    public function test_a_changed_or_unsigned_link_is_refused(): void
    {
        $mine = Subscription::factory()->create();
        $other = Subscription::factory()->create();

        $forged = str_replace("/unsubscribe/{$mine->id}?", "/unsubscribe/{$other->id}?", $mine->unsubscribeUrl());
        $this->get($forged)->assertForbidden();
        $this->post($forged)->assertForbidden();
        $this->post("/newsletter/unsubscribe/{$other->id}")->assertForbidden();
        $this->assertTrue($other->fresh()->is_active);
    }

    public function test_the_link_works_on_http_https_and_www(): void
    {
        $subscription = Subscription::factory()->create();
        $path = parse_url($subscription->unsubscribeUrl(), PHP_URL_PATH) . '?' . parse_url($subscription->unsubscribeUrl(), PHP_URL_QUERY);

        $this->get('https://www.supplement-station.test' . $path)->assertOk();
    }

    // ---------- contact form ----------

    public function test_contact_message_reaches_the_store_and_replies_go_to_the_visitor(): void
    {
        Mail::fake();
        $this->withoutDefer();

        $this->post('/contact/send', ['name' => 'Maya', 'email' => 'maya@example.com', 'phone' => '70000000', 'message' => 'Do you have creatine?'])
            ->assertRedirect()->assertSessionHas('success');

        Mail::assertSent(ContactMail::class, fn (ContactMail $mail) => $mail->hasTo(config('mail.admin_address'))
            && $mail->hasReplyTo('maya@example.com', 'Maya')
            && $mail->hasSubject('Contact form: Maya')
            && str_contains($mail->render(), 'Do you have creatine?'));
    }

    public function test_contact_form_is_validated_and_rate_limited(): void
    {
        Mail::fake();
        $this->post('/contact/send', ['name' => 'a', 'email' => 'bad', 'phone' => '1', 'message' => 'x'])->assertSessionHasErrors('email');
        for ($i = 0; $i < 2; $i++) {
            $this->post('/contact/send', ['name' => 'a', 'email' => 'a@example.com', 'phone' => '1', 'message' => 'x'])->assertRedirect();
        }
        $this->post('/contact/send', ['name' => 'a', 'email' => 'a@example.com', 'phone' => '1', 'message' => 'x'])->assertStatus(429);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Mail\NewOrderMail;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_emails_go_to_the_store_mailbox_until_the_admin_saves_others(): void
    {
        $this->assertSame([config('mail.admin_address')], NewOrderMail::recipients());
        $this->admin()->get('/admin/settings')->assertOk()->assertSee(config('mail.admin_address'));

        $this->put('/admin/settings', ['order_emails' => " Owner@Example.com;partner@example.com\nowner@example.com "])
            ->assertRedirect('/admin/settings')->assertSessionHas('success');

        $this->assertSame(['owner@example.com', 'partner@example.com'], NewOrderMail::recipients());
        $this->assertSame('owner@example.com, partner@example.com', Setting::read(NewOrderMail::RECIPIENTS_SETTING));
        $this->get('/admin/settings')->assertSee('owner@example.com, partner@example.com');
    }

    public function test_bad_addresses_are_refused_and_the_saved_ones_kept(): void
    {
        Setting::write(NewOrderMail::RECIPIENTS_SETTING, 'owner@example.com');
        $this->admin();

        $this->put('/admin/settings', ['order_emails' => 'owner@example.com, not-an-email'])
            ->assertSessionHasErrors(['order_emails' => '"not-an-email" is not a valid email address.']);
        $this->put('/admin/settings', ['order_emails' => ' , '])
            ->assertSessionHasErrors(['order_emails' => 'Enter at least one email address.']);
        $this->put('/admin/settings', ['order_emails' => 'a@x.com, b@x.com, c@x.com, d@x.com, e@x.com, f@x.com'])
            ->assertSessionHasErrors('order_emails');

        $this->assertSame(['owner@example.com'], NewOrderMail::recipients());
    }

    public function test_the_test_email_goes_to_the_saved_addresses(): void
    {
        Setting::write(NewOrderMail::RECIPIENTS_SETTING, 'owner@example.com, partner@example.com');

        $this->admin()->post('/admin/settings/test-email')->assertRedirect('/admin/settings')->assertSessionHas('success');

        $sent = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $sent);
        $to = array_map(fn ($address) => $address->getAddress(), $sent[0]->getOriginalMessage()->getTo());
        $this->assertSame(['owner@example.com', 'partner@example.com'], $to);
    }

    public function test_a_failing_mail_server_shows_the_admin_an_error(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 2]);

        $this->admin()->post('/admin/settings/test-email')->assertRedirect('/admin/settings')
            ->assertSessionHas('error', fn ($message) => str_starts_with($message, 'The test email could not be sent'));
    }

    public function test_settings_need_a_login(): void
    {
        $this->get('/admin/settings')->assertRedirect('/login');
        $this->put('/admin/settings', ['order_emails' => 'thief@example.com'])->assertRedirect('/login');
        $this->post('/admin/settings/test-email')->assertRedirect('/login');

        $this->assertNull(Setting::read(NewOrderMail::RECIPIENTS_SETTING));
        $this->assertCount(0, app('mailer')->getSymfonyTransport()->messages());
    }
}

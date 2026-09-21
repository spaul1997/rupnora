<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_subscribe_to_the_newsletter(): void
    {
        $this->postJson(route('newsletter-subscriptions.store'), [
            'email' => 'Ananya@Example.com',
        ])->assertCreated()
            ->assertJson([
                'message' => 'Thank you for subscribing!',
                'subscribed' => true,
            ]);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'ananya@example.com',
        ]);
    }

    public function test_subscribing_twice_does_not_create_a_duplicate(): void
    {
        $this->postJson(route('newsletter-subscriptions.store'), [
            'email' => 'ananya@example.com',
        ])->assertCreated();

        $this->postJson(route('newsletter-subscriptions.store'), [
            'email' => 'ANANYA@example.com',
        ])->assertOk()
            ->assertJsonPath('message', 'You are already subscribed.');

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_subscription_requires_a_valid_email_address(): void
    {
        $this->postJson(route('newsletter-subscriptions.store'), [
            'email' => 'not-an-email',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_footer_contains_the_live_subscription_form(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('newsletter-subscriptions.store'), escape: false)
            ->assertSee('newsletterForm', escape: false);
    }
}

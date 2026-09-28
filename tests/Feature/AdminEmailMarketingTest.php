<?php

namespace Tests\Feature;

use App\Jobs\SendMarketingCampaignEmail;
use App\Mail\MarketingCampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\Influencer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminEmailMarketingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_composer_with_all_templates_and_required_cc(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.email-campaigns.create'))
            ->assertOk()
            ->assertSee('New Offer Launch')
            ->assertSee('New Design Launch')
            ->assertSee('Influencer Collaboration Proposal')
            ->assertSee('Influencer Agreement')
            ->assertSee('rupnorafashion@gmail.com');
    }

    public function test_admin_can_view_campaign_delivery_tracking(): void
    {
        $campaign = $this->campaign(['total_recipient_count' => 1, 'queued_count' => 1]);
        $campaign->recipients()->create([
            'name' => 'Ananya Rao',
            'email' => 'ananya@example.com',
            'source' => 'customer',
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.email-campaigns.show', $campaign))
            ->assertOk()
            ->assertSee($campaign->name)
            ->assertSee('ananya@example.com')
            ->assertSee('rupnorafashion@gmail.com');
    }

    public function test_admin_can_queue_a_campaign_for_multiple_deduplicated_recipients(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $customer = User::factory()->create([
            'name' => 'Ananya Rao',
            'email' => 'ananya@example.com',
            'role' => 'customer',
            'is_active' => true,
        ]);
        $influencer = $this->influencer();

        $this->actingAs($admin)
            ->post(route('admin.email-campaigns.store'), [
                ...$this->campaignData(),
                'customer_ids' => [$customer->id],
                'influencer_ids' => [$influencer->id],
                'manual_emails' => "ananya@example.com\nextra@example.com",
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $campaign = EmailCampaign::firstOrFail();

        $this->assertSame('rupnorafashion@gmail.com', $campaign->cc_email);
        $this->assertSame(3, $campaign->total_recipient_count);
        $this->assertSame(3, $campaign->queued_count);
        $this->assertDatabaseHas('email_campaign_recipients', ['email' => 'ananya@example.com', 'source' => 'customer']);
        $this->assertDatabaseHas('email_campaign_recipients', ['email' => 'creator@example.com', 'source' => 'influencer']);
        $this->assertDatabaseHas('email_campaign_recipients', ['email' => 'extra@example.com', 'source' => 'manual']);
        Queue::assertPushedOn('emails', SendMarketingCampaignEmail::class);
        Queue::assertPushed(SendMarketingCampaignEmail::class, 3);
    }

    public function test_campaign_requires_at_least_one_recipient(): void
    {
        Queue::fake();

        $this->actingAs($this->admin())
            ->from(route('admin.email-campaigns.create'))
            ->post(route('admin.email-campaigns.store'), $this->campaignData())
            ->assertRedirect(route('admin.email-campaigns.create'))
            ->assertSessionHasErrors(['recipients']);

        $this->assertDatabaseCount('email_campaigns', 0);
        Queue::assertNothingPushed();
    }

    public function test_queued_job_sends_individual_email_with_required_cc_and_tracks_delivery(): void
    {
        Mail::fake();

        $campaign = $this->campaign();
        $recipient = $campaign->recipients()->create([
            'name' => 'Ananya Rao',
            'email' => 'ananya@example.com',
            'source' => 'customer',
            'status' => 'queued',
            'queued_at' => now(),
        ]);
        $campaign->update(['total_recipient_count' => 1, 'queued_count' => 1]);

        (new SendMarketingCampaignEmail($recipient->id))->handle();

        Mail::assertSent(MarketingCampaignMail::class, function (MarketingCampaignMail $mail) {
            return $mail->hasTo('ananya@example.com')
                && $mail->hasCc('rupnorafashion@gmail.com');
        });

        $this->assertDatabaseHas('email_campaign_recipients', [
            'id' => $recipient->id,
            'status' => 'sent',
            'attempts' => 1,
        ]);
        $this->assertSame('completed', $campaign->refresh()->status);
        $this->assertSame(1, $campaign->sent_count);
        $this->assertSame(0, $campaign->queued_count);
    }

    public function test_all_four_branded_templates_render_in_preview(): void
    {
        $admin = $this->admin();

        foreach (array_keys(EmailCampaign::TEMPLATES) as $template) {
            $campaign = $this->campaign(['template_type' => $template, 'name' => $template]);

            $this->actingAs($admin)
                ->get(route('admin.email-campaigns.preview', $campaign))
                ->assertOk()
                ->assertSee('RUPNORA')
                ->assertSee('Celebrate with timeless jewellery');
        }
    }

    public function test_admin_can_retry_failed_recipient(): void
    {
        Queue::fake();

        $campaign = $this->campaign(['status' => 'failed', 'failed_count' => 1]);
        $recipient = $campaign->recipients()->create([
            'email' => 'retry@example.com',
            'source' => 'manual',
            'status' => 'failed',
            'attempts' => 3,
            'failed_at' => now(),
            'failure_message' => 'Temporary SMTP error',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.email-campaigns.recipients.retry', [$campaign, $recipient]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('queued', $recipient->refresh()->status);
        Queue::assertPushed(SendMarketingCampaignEmail::class, fn ($job) => $job->recipientId === $recipient->id);
    }

    public function test_non_admin_cannot_access_email_marketing(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($customer)
            ->get(route('admin.email-campaigns.index'))
            ->assertForbidden();
    }

    private function campaignData(): array
    {
        return [
            'name' => 'Festive Offer Launch',
            'template_type' => 'new-offer-launch',
            'subject' => 'A special Rupnora offer',
            'preheader' => 'A limited offer for you.',
            'eyebrow' => 'Exclusive Offer',
            'headline' => 'Celebrate with timeless jewellery',
            'body' => 'Discover thoughtfully crafted jewellery with a special offer available for a limited time.',
            'highlight_text' => 'Use code RUPNORA10',
            'cta_label' => 'Shop Now',
            'cta_url' => 'https://rupnora.in/collections',
        ];
    }

    private function campaign(array $overrides = []): EmailCampaign
    {
        return EmailCampaign::create(array_merge([
            'created_by' => $this->admin()->id,
            ...$this->campaignData(),
            'cc_email' => 'rupnorafashion@gmail.com',
            'status' => 'queued',
            'queued_at' => now(),
        ], $overrides));
    }

    private function influencer(): Influencer
    {
        return Influencer::create([
            'reference_no' => 'RPN-INF-2026-MAILTEST',
            'full_name' => 'Creator One',
            'email' => 'creator@example.com',
            'phone' => '+91 98765 43210',
            'location' => 'Mumbai',
            'primary_platform' => 'instagram',
            'social_handle' => '@creator',
            'followers_count' => 25000,
            'content_niche' => 'Fashion and jewellery',
            'status' => 'approved',
            'is_active' => true,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmailCampaignRequest;
use App\Jobs\SendMarketingCampaignEmail;
use App\Mail\MarketingCampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\Influencer;
use App\Models\User;
use App\Support\ProductImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmailCampaignController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $template = (string) $request->query('template');
        $status = (string) $request->query('status');

        $campaigns = EmailCampaign::query()
            ->with('creator:id,name')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('headline', 'like', "%{$search}%");
            }))
            ->when(array_key_exists($template, EmailCampaign::TEMPLATES), fn ($query) => $query->where('template_type', $template))
            ->when(array_key_exists($status, EmailCampaign::STATUSES), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.email-campaigns.index', [
            'campaigns' => $campaigns,
            'templates' => EmailCampaign::TEMPLATES,
            'statuses' => EmailCampaign::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('admin.email-campaigns.create', [
            'templates' => EmailCampaign::TEMPLATES,
            'customers' => User::customers()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']),
            'influencers' => Influencer::query()->orderBy('full_name')->get(['id', 'full_name', 'email', 'status', 'primary_platform']),
            'ccEmail' => config('marketing.cc_email'),
        ]);
    }

    public function store(StoreEmailCampaignRequest $request): RedirectResponse
    {
        $recipients = $this->resolveRecipients($request);
        $data = Arr::except($request->validated(), [
            'image',
            'customer_ids',
            'influencer_ids',
            'manual_emails',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = ProductImageOptimizer::store($request->file('image'), 'email-campaigns');
        }

        $campaign = DB::transaction(function () use ($request, $data, $recipients) {
            $campaign = EmailCampaign::create([
                ...$data,
                'created_by' => $request->user()->id,
                'cc_email' => config('marketing.cc_email'),
                'status' => 'queued',
                'total_recipient_count' => count($recipients),
                'queued_count' => count($recipients),
                'queued_at' => now(),
            ]);

            $campaign->recipients()->createMany(array_map(fn (array $recipient) => [
                ...$recipient,
                'status' => 'queued',
                'queued_at' => now(),
            ], $recipients));

            return $campaign;
        });

        $campaign->recipients()->pluck('id')->each(
            fn (int $recipientId) => SendMarketingCampaignEmail::dispatch($recipientId)
        );

        return redirect()->route('admin.email-campaigns.show', $campaign)
            ->with('success', count($recipients).' email'.(count($recipients) === 1 ? '' : 's').' queued successfully.');
    }

    public function show(EmailCampaign $emailCampaign): View
    {
        $emailCampaign->load('creator:id,name,email');

        return view('admin.email-campaigns.show', [
            'campaign' => $emailCampaign,
            'recipients' => $emailCampaign->recipients()->orderByDesc('id')->paginate(50),
            'templates' => EmailCampaign::TEMPLATES,
        ]);
    }

    public function preview(EmailCampaign $emailCampaign): Response
    {
        return response((new MarketingCampaignMail($emailCampaign, 'Rupnora Creator'))->render());
    }

    public function retryFailed(EmailCampaign $emailCampaign): RedirectResponse
    {
        $recipientIds = $emailCampaign->recipients()->where('status', 'failed')->pluck('id');

        if ($recipientIds->isEmpty()) {
            return back()->with('error', 'This campaign has no failed emails to retry.');
        }

        $emailCampaign->recipients()->whereIn('id', $recipientIds)->update([
            'status' => 'queued',
            'queued_at' => now(),
            'failed_at' => null,
            'failure_message' => null,
        ]);
        $emailCampaign->refreshDeliveryStats();

        $recipientIds->each(fn (int $recipientId) => SendMarketingCampaignEmail::dispatch($recipientId));

        return back()->with('success', $recipientIds->count().' failed email'.($recipientIds->count() === 1 ? '' : 's').' queued again.');
    }

    public function retryRecipient(EmailCampaign $emailCampaign, EmailCampaignRecipient $recipient): RedirectResponse
    {
        abort_unless($recipient->email_campaign_id === $emailCampaign->id, 404);

        if ($recipient->status !== 'failed') {
            return back()->with('error', 'Only failed emails can be retried.');
        }

        $recipient->update([
            'status' => 'queued',
            'queued_at' => now(),
            'failed_at' => null,
            'failure_message' => null,
        ]);
        $emailCampaign->refreshDeliveryStats();
        SendMarketingCampaignEmail::dispatch($recipient->id);

        return back()->with('success', 'The email to '.$recipient->email.' was queued again.');
    }

    protected function resolveRecipients(StoreEmailCampaignRequest $request): array
    {
        $recipients = [];

        User::customers()
            ->where('is_active', true)
            ->whereIn('id', (array) $request->validated('customer_ids', []))
            ->get(['name', 'email'])
            ->each(function (User $customer) use (&$recipients) {
                $recipients[strtolower($customer->email)] = [
                    'name' => $customer->name,
                    'email' => strtolower($customer->email),
                    'source' => 'customer',
                ];
            });

        Influencer::query()
            ->whereIn('id', (array) $request->validated('influencer_ids', []))
            ->get(['full_name', 'email'])
            ->each(function (Influencer $influencer) use (&$recipients) {
                $recipients[strtolower($influencer->email)] = [
                    'name' => $influencer->full_name,
                    'email' => strtolower($influencer->email),
                    'source' => 'influencer',
                ];
            });

        foreach ($request->manualEmails() as $email) {
            $recipients[$email] ??= [
                'name' => null,
                'email' => $email,
                'source' => 'manual',
            ];
        }

        return array_values($recipients);
    }
}

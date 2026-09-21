<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsletterSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => Str::lower(trim($data['email']))],
            ['subscribed_at' => now()],
        );

        $message = $subscriber->wasRecentlyCreated
            ? 'Thank you for subscribing!'
            : 'You are already subscribed.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'subscribed' => true,
            ], $subscriber->wasRecentlyCreated ? 201 : 200);
        }

        return back()->with('success', $message);
    }
}

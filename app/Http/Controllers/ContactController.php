<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\WebsiteSetting;
use App\Support\Catalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $faqs = Faq::active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(8)
            ->get()
            ->map(fn (Faq $faq) => ['q' => $faq->question, 'a' => $faq->answer])
            ->all();

        return view('pages.contact', [
            'title' => 'Contact Us',
            'faqs' => $faqs ?: Catalog::faqs(),
            'settings' => WebsiteSetting::current(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $contact = ContactMessage::create([
            ...$data,
            'user_id' => $request->user()?->id,
            'ticket_no' => ContactMessage::generateTicketNumber(),
            'priority' => 'normal',
            'status' => 'new',
        ]);

        return redirect()
            ->to(route('contact').'#contact-form')
            ->with('success', 'Thank you for reaching out. Your request number is '.$contact->ticket_no.'.');
    }
}

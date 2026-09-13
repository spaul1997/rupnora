<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $contacts = ContactMessage::query()
            ->with('assignedTo')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('ticket_no', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->when($request->priority, fn ($q, $p) => $q->where('priority', $p))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contacts.index', compact('contacts'));
    }

    public function show(ContactMessage $contact): View
    {
        $contact->load('assignedTo');
        $admins = User::admins()->orderBy('name')->get(['id', 'name']);

        return view('admin.contacts.show', compact('contact', 'admins'));
    }

    public function update(Request $request, ContactMessage $contact): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $contact->update($data);

        return back()->with('success', 'Contact request updated successfully.');
    }

    public function reply(Request $request, ContactMessage $contact): RedirectResponse
    {
        $data = $request->validate([
            'admin_reply' => ['required', 'string', 'max:2000'],
        ]);

        $contact->update([
            'admin_reply' => $data['admin_reply'],
            'replied_at' => now(),
            'status' => $contact->status === 'new' ? 'open' : $contact->status,
        ]);

        return back()->with('success', 'Reply sent successfully.');
    }

    public function assign(Request $request, ContactMessage $contact): RedirectResponse
    {
        $data = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $contact->update(['assigned_to' => $data['assigned_to'], 'status' => 'in_progress']);

        return back()->with('success', 'Ticket assigned successfully.');
    }

    public function updatePriority(Request $request, ContactMessage $contact): RedirectResponse
    {
        $data = $request->validate([
            'priority' => ['required', 'in:low,normal,high,urgent'],
        ]);

        $contact->update($data);

        return back()->with('success', 'Priority updated successfully.');
    }

    public function updateStatus(Request $request, ContactMessage $contact): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,open,in_progress,waiting_customer,resolved,closed'],
        ]);

        $contact->update($data);

        return back()->with('success', 'Status updated successfully.');
    }

    public function close(ContactMessage $contact): RedirectResponse
    {
        $contact->update(['status' => 'closed']);

        return back()->with('success', 'Ticket closed successfully.');
    }

    public function destroy(ContactMessage $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')->with('success', 'Contact request deleted successfully.');
    }
}

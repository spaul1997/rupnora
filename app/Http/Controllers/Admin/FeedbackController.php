<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $feedbacks = Feedback::query()
            ->with(['customer', 'assignedTo'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.feedback.index', compact('feedbacks'));
    }

    public function show(Feedback $feedback): View
    {
        $feedback->load(['customer', 'assignedTo']);
        $admins = User::admins()->orderBy('name')->get(['id', 'name']);

        return view('admin.feedback.show', compact('feedback', 'admins'));
    }

    public function update(Request $request, Feedback $feedback): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,in_progress,resolved,closed'],
        ]);

        $feedback->update($data);

        return back()->with('success', 'Feedback updated successfully.');
    }

    public function assign(Request $request, Feedback $feedback): RedirectResponse
    {
        $data = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $feedback->update(['assigned_to' => $data['assigned_to'], 'status' => 'in_progress']);

        return back()->with('success', 'Feedback assigned successfully.');
    }

    public function addNote(Request $request, Feedback $feedback): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['required', 'string', 'max:2000'],
        ]);

        $feedback->update($data);

        return back()->with('success', 'Note added successfully.');
    }

    public function resolve(Feedback $feedback): RedirectResponse
    {
        $feedback->update(['status' => 'resolved']);

        return back()->with('success', 'Feedback resolved successfully.');
    }

    public function close(Feedback $feedback): RedirectResponse
    {
        $feedback->update(['status' => 'closed']);

        return back()->with('success', 'Feedback closed successfully.');
    }

    public function destroy(Feedback $feedback): RedirectResponse
    {
        $feedback->delete();

        return redirect()->route('admin.feedbacks.index')->with('success', 'Feedback deleted successfully.');
    }
}

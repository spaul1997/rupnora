<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = User::query()
            ->customers()
            ->withCount('orders')
            ->withSum(['orders as total_spend' => fn ($q) => $q->where('payment_status', 'paid')], 'grand_total')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer): View
    {
        abort_unless($customer->role === 'customer', 404);

        $customer->loadCount('orders');
        $orders = $customer->orders()->latest()->take(10)->get();
        $reviews = $customer->reviews()->with('product')->latest()->take(10)->get();
        $feedbacks = $customer->feedbacks()->latest()->take(10)->get();
        $totalSpend = $customer->orders()->where('payment_status', 'paid')->sum('grand_total');

        return view('admin.customers.show', compact('customer', 'orders', 'reviews', 'feedbacks', 'totalSpend'));
    }

    public function edit(User $customer): View
    {
        abort_unless($customer->role === 'customer', 404);

        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->role === 'customer', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$customer->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $customer->update($data);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function toggleActive(User $customer): RedirectResponse
    {
        abort_unless($customer->role === 'customer', 404);

        $customer->update(['is_active' => ! $customer->is_active]);

        return back()->with('success', 'Customer status updated successfully.');
    }
}

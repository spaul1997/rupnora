@extends('admin.layouts.app')

@section('title', 'Edit Customer')

@section('content')
    <x-admin.page-header title="Edit Customer" :breadcrumb="[['label' => 'Customers', 'url' => route('admin.customers.index')], ['label' => $customer->name]]" />

    <div class="max-w-xl">
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="admin-card space-y-5 p-6">
            @csrf
            @method('PUT')
            <x-admin.form.input label="Name" name="name" :value="$customer->name" required />
            <x-admin.form.input label="Email" name="email" type="email" :value="$customer->email" required />
            <x-admin.form.input label="Phone" name="phone" :value="$customer->phone" />
            <label class="flex items-center gap-2.5 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $customer->is_active)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                Active
            </label>
            <button type="submit" class="admin-btn-primary w-full">Save Changes</button>
        </form>
    </div>
@endsection

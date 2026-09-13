@extends('admin.layouts.app')

@section('title', 'Website Settings')

@section('content')
    <x-admin.page-header title="Website Settings" description="Manage contact details shown on the storefront." />

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="admin-card space-y-5 p-6">
                <h3 class="text-sm font-semibold text-gray-900">Company Information</h3>
                <x-admin.form.input label="Company Name" name="company_name" :value="$settings->company_name" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-admin.form.input label="Support Email" name="support_email" type="email" :value="$settings->support_email" />
                    <x-admin.form.input label="Sales Email" name="sales_email" type="email" :value="$settings->sales_email" />
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-admin.form.input label="Phone" name="phone" :value="$settings->phone" />
                    <x-admin.form.input label="WhatsApp" name="whatsapp" :value="$settings->whatsapp" />
                </div>
                <x-admin.form.textarea label="Address" name="address" :value="$settings->address" :rows="3" />
                <x-admin.form.input label="Business Hours" name="business_hours" :value="$settings->business_hours" />
                <x-admin.form.input label="Google Map URL" name="google_map_url" :value="$settings->google_map_url" />
            </div>

            <div class="admin-card space-y-5 p-6">
                <h3 class="text-sm font-semibold text-gray-900">Social Media</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-admin.form.input label="Facebook" name="facebook" :value="$settings->facebook" />
                    <x-admin.form.input label="Instagram" name="instagram" :value="$settings->instagram" />
                    <x-admin.form.input label="LinkedIn" name="linkedin" :value="$settings->linkedin" />
                    <x-admin.form.input label="YouTube" name="youtube" :value="$settings->youtube" />
                </div>
            </div>

            <button type="submit" class="admin-btn-primary">Save Settings</button>
        </form>
    </div>
@endsection

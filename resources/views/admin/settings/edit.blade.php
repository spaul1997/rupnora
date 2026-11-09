@extends('admin.layouts.app')

@section('title', 'Website Settings')

@section('content')
    <x-admin.page-header title="Website Settings" description="Manage storefront contact details and delivery pricing." />

    <form method="POST" action="{{ route('admin.settings.update') }}" class="grid max-w-6xl items-start gap-4 xl:grid-cols-2 [&_.admin-input]:py-2 [&_.admin-label]:mb-1 [&_.admin-textarea]:py-2">
        @csrf
        @method('PUT')

        <section class="admin-card min-w-0 p-4 sm:p-5" aria-labelledby="company-settings-heading">
            <h2 id="company-settings-heading" class="text-sm font-semibold text-gray-900">Company Information</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <x-admin.form.input label="Company Name" name="company_name" :value="$settings->company_name" />
                <x-admin.form.input label="Business Hours" name="business_hours" :value="$settings->business_hours" />
                <x-admin.form.input label="Support Email" name="support_email" type="email" :value="$settings->support_email" />
                <x-admin.form.input label="Sales Email" name="sales_email" type="email" :value="$settings->sales_email" />
                <x-admin.form.input label="Phone" name="phone" :value="$settings->phone" />
                <x-admin.form.input label="WhatsApp" name="whatsapp" :value="$settings->whatsapp" />
                <div class="sm:col-span-2">
                    <x-admin.form.textarea label="Address" name="address" :value="$settings->address" :rows="2" />
                </div>
                <div class="sm:col-span-2">
                    <x-admin.form.input label="Google Map URL" name="google_map_url" :value="$settings->google_map_url" />
                </div>
            </div>
        </section>

        <section class="admin-card min-w-0 p-4 sm:p-5" aria-labelledby="delivery-settings-heading">
            <h2 id="delivery-settings-heading" class="text-sm font-semibold text-gray-900">Delivery</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <x-admin.form.input label="Customer Shipping Share (%)" name="shipping_per_limit" type="number" :value="$settings->shipping_per_limit" :required="true" min="0" max="100" step="0.01" help="10 = ₹8.50 on an ₹85 courier rate. 100 = full rate; 0 = free." />
                <x-admin.form.input label="Free Shipping Order Threshold (INR)" name="free_shipping_threshold" type="number" :value="$settings->free_shipping_threshold" :required="true" min="0" max="99999999.99" step="0.01" help="Free shipping when the discounted cart reaches this amount. 0 = always free." />
                <x-admin.form.input label="Express Delivery Price (INR)" name="express_delivery_charge" type="number" :value="$settings->express_delivery_charge" :required="true" min="0" max="99999999.99" step="0.01" help="Added for express delivery. 0 = no extra charge." />
                <x-admin.form.input label="Cash on Delivery Order Limit (INR)" name="cod_order_limit" type="number" :value="$settings->cod_order_limit" :required="true" min="0" max="99999999.99" step="0.01" help="Final total must be below this limit. 0 = disabled." />
                <x-admin.form.input label="Return / Refund Allowance (Days)" name="return_allow" type="number" :value="$settings->return_allow" :required="true" min="0" max="365" step="1" help="Days after delivery before affiliate commission is released." />
            </div>
        </section>

        <section class="admin-card min-w-0 p-4 sm:p-5 xl:col-span-2" aria-labelledby="social-settings-heading">
            <h2 id="social-settings-heading" class="text-sm font-semibold text-gray-900">Social Media</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <x-admin.form.input label="Facebook" name="facebook" :value="$settings->facebook" />
                <x-admin.form.input label="Instagram" name="instagram" :value="$settings->instagram" />
                <x-admin.form.input label="LinkedIn" name="linkedin" :value="$settings->linkedin" />
                <x-admin.form.input label="YouTube" name="youtube" :value="$settings->youtube" />
            </div>
        </section>

        <div class="sticky bottom-0 z-10 flex justify-end border-t border-gray-200 bg-gray-50/95 py-3 backdrop-blur-sm xl:col-span-2">
            <button type="submit" class="admin-btn-primary w-full sm:w-auto">Save Settings</button>
        </div>
    </form>
@endsection

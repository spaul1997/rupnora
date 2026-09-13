@props([
    'subtotal' => 0,
    'discount' => 0,
    'shipping' => 0,
    'tax' => 0,
    'couponDiscount' => 0,
    'showCoupon' => true,
    'ctaLabel' => 'Proceed to Checkout',
    'ctaUrl' => null,
])

@php
    $total = $subtotal - $discount - $couponDiscount + $shipping + $tax;
@endphp

<div class="card-luxe p-6">
    <h3 class="font-display text-lg text-charcoal">Order Summary</h3>

    @if ($showCoupon)
        <div class="mt-4 flex gap-2" x-data="{ code: '' }">
            <input type="text" x-model="code" placeholder="Enter Coupon Code" class="input-luxe flex-1 !py-2.5 text-sm">
            <button type="button" class="btn-secondary !px-5 !py-2.5 text-[11px]">Apply</button>
        </div>
    @endif

    <div class="mt-5 space-y-3 border-t border-line pt-5 text-sm">
        <div class="flex justify-between text-muted">
            <span>Subtotal</span>
            <span class="text-charcoal">₹{{ number_format($subtotal) }}</span>
        </div>
        @if ($discount > 0)
            <div class="flex justify-between text-muted">
                <span>Discount</span>
                <span class="text-success">&minus;₹{{ number_format($discount) }}</span>
            </div>
        @endif
        @if ($couponDiscount > 0)
            <div class="flex justify-between text-muted">
                <span>Coupon Applied</span>
                <span class="text-success">&minus;₹{{ number_format($couponDiscount) }}</span>
            </div>
        @endif
        <div class="flex justify-between text-muted">
            <span>Shipping</span>
            <span class="text-charcoal">{{ $shipping > 0 ? '₹' . number_format($shipping) : 'Free' }}</span>
        </div>
        <div class="flex justify-between text-muted">
            <span>Tax (GST incl.)</span>
            <span class="text-charcoal">₹{{ number_format($tax) }}</span>
        </div>
    </div>

    <div class="mt-5 flex justify-between border-t border-line pt-5">
        <span class="font-display text-lg text-charcoal">Total</span>
        <span class="font-display text-lg text-charcoal">₹{{ number_format($total) }}</span>
    </div>

    @if ($ctaUrl)
        <a href="{{ $ctaUrl }}" class="btn-primary mt-6 w-full">{{ $ctaLabel }}</a>
    @endif

    <div class="mt-5 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-line pt-5 text-[11px] text-muted">
        <span class="flex items-center gap-1.5"><svg class="h-3.5 w-3.5 text-champagne-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4z" /></svg> Secure Checkout</span>
        <span class="flex items-center gap-1.5"><svg class="h-3.5 w-3.5 text-champagne-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h13v10H3zM16 10h3l2 3v4h-5z" /><circle cx="7.5" cy="18" r="1.5" /><circle cx="17.5" cy="18" r="1.5" /></svg> Free Shipping</span>
        <span class="flex items-center gap-1.5"><svg class="h-3.5 w-3.5 text-champagne-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V4" /></svg> Easy Returns</span>
    </div>
</div>

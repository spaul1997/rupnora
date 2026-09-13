<x-layouts.app :title="$title">
    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Refund Policy']]" />
    </div>

    <section class="container-luxe py-10 sm:py-14">
        <div class="mx-auto max-w-3xl">
            <span class="eyebrow">Rupnora Policies</span>
            <h1 class="font-display mt-2 text-4xl text-charcoal sm:text-5xl">Refund Policy</h1>
            <p class="mt-3 text-sm text-muted">Last updated: September 13, 2026</p>
            <p class="mt-6 text-base leading-relaxed text-charcoal-soft">
                Rupnora wants every jewellery purchase to feel considered and confident. If something is not right with your order, please contact us quickly so we can help.
            </p>

            <div class="mt-10 space-y-8 text-sm leading-relaxed text-muted sm:text-base">
                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">3-Day Return Window</h2>
                    <p class="mt-3">You may request a return within 3 days from the date your order is delivered. Return requests made after 3 days may not be accepted.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Return Eligibility</h2>
                    <p class="mt-3">To be eligible, the jewellery must be unused, unworn, undamaged, and returned with the original invoice, packaging, tags, certificates, warranty cards, and any included accessories or gifts.</p>
                    <p class="mt-3">Returned items are accepted only after Rupnora completes a quality check and confirms that the item matches the original order condition.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Items Not Eligible For Return</h2>
                    <p class="mt-3">Customised, engraved, resized, altered, made-to-order, worn, damaged, or tampered jewellery cannot be returned unless the item was delivered damaged, defective, or different from the confirmed order.</p>
                    <p class="mt-3">Products missing original tags, certificates, invoices, packaging, or accessories may also be rejected during quality check.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">How To Request A Return</h2>
                    <p class="mt-3">Contact Rupnora through our <a href="{{ route('contact') }}" class="font-medium text-champagne-dark hover:underline">Contact Us</a> page within 3 days of delivery. Please include your order number, registered phone number or email, reason for return, and clear photos if the item is damaged or incorrect.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Refund Process</h2>
                    <p class="mt-3">Once the returned item is received and approved after quality check, the refund will be initiated to the original payment method. Depending on your bank or payment provider, the amount may take additional business days to reflect in your account.</p>
                    <p class="mt-3">Cash-on-delivery refunds, where applicable, may require verified bank account details from the customer.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Damaged Or Incorrect Items</h2>
                    <p class="mt-3">If your order arrives damaged, defective, or different from what you ordered, contact us within 3 days of delivery with photos and packaging details. We will review the issue and offer a replacement, exchange, repair, or refund depending on availability and inspection.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Shipping And Charges</h2>
                    <p class="mt-3">Return pickup availability may vary by location. Shipping charges, gift wrapping, express delivery charges, and other service fees may be non-refundable unless the return is due to a Rupnora error.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Contact Us</h2>
                    <p class="mt-3">For return, exchange, or refund help, please contact Rupnora through our <a href="{{ route('contact') }}" class="font-medium text-champagne-dark hover:underline">Contact Us</a> page.</p>
                </section>
            </div>
        </div>
    </section>
</x-layouts.app>

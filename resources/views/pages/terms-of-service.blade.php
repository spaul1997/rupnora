<x-layouts.app :title="$title">
    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Terms of Service']]" />
    </div>

    <section class="container-luxe py-10 sm:py-14">
        <div class="mx-auto max-w-5xl my-2">
            <span class="eyebrow">Rupnora Policies</span>
            <h1 class="font-display mt-2 text-4xl text-charcoal sm:text-5xl">Terms of Service</h1>
            <p class="mt-3 text-sm text-muted">Last updated: September 13, 2026</p>
            <p class="mt-6 text-base leading-relaxed text-charcoal-soft">
                These Terms of Service apply when you visit Rupnora, create an account, place an order, or use any feature of our online jewellery store.
            </p>

            <div class="mt-10 space-y-8 text-sm leading-relaxed text-muted sm:text-base">
                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Using Rupnora</h2>
                    <p class="mt-3">You agree to use this website for lawful shopping and account purposes only. You must not misuse the website, attempt unauthorised access, copy site content for commercial use, or interfere with checkout, payment, inventory, or order systems.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Accounts And Order Details</h2>
                    <p class="mt-3">You are responsible for keeping your account login details secure and for providing accurate billing, shipping, and contact information. Rupnora may contact you to verify order or delivery details before dispatch.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Products, Pricing And Availability</h2>
                    <p class="mt-3">We try to display jewellery details, images, prices, metal purity, stone details, weights, and availability as accurately as possible. Minor differences in colour, size, finish, weight, or appearance may occur because jewellery is photographed under lighting and viewed on different screens.</p>
                    <p class="mt-3">Prices, offers, stock, and product details may change without prior notice. If a pricing or availability error affects your order, we may contact you to revise, cancel, or refund the order.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Payments And Orders</h2>
                    <p class="mt-3">Orders are confirmed only after successful payment or accepted cash-on-delivery confirmation, where available. Rupnora may cancel orders for failed payment, suspected fraud, incorrect product information, delivery limitations, or stock issues.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Shipping, Returns And Refunds</h2>
                    <p class="mt-3">Shipping timelines are estimates and may vary by location, courier availability, verification, or external delays. Returns and refunds are handled according to our <a href="{{ route('refund-policy') }}" class="font-medium text-champagne-dark hover:underline">Refund Policy</a>, including a 3-day return request window from delivery.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Custom And Personalised Jewellery</h2>
                    <p class="mt-3">Customised, engraved, resized, altered, or specially made pieces may not be eligible for cancellation, return, or exchange unless they arrive damaged, defective, or different from the confirmed order.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Content And Reviews</h2>
                    <p class="mt-3">If you submit reviews, photos, feedback, or messages, you confirm that the content is accurate, lawful, and does not violate another person's rights. Rupnora may moderate or remove content that is abusive, misleading, promotional, or irrelevant.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Contact Us</h2>
                    <p class="mt-3">For questions about these terms, please contact Rupnora through our <a href="{{ route('contact') }}" class="font-medium text-champagne-dark hover:underline">Contact Us</a> page.</p>
                </section>
            </div>
        </div>
    </section>
</x-layouts.app>

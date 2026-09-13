<x-layouts.app :title="$title">
    <div class="container-luxe pt-6">
        <x-ui.breadcrumb :trail="[['label' => 'Privacy Policy']]" />
    </div>

    <section class="container-luxe py-10 sm:py-14">
        <div class="mx-auto max-w-3xl">
            <span class="eyebrow">Rupnora Policies</span>
            <h1 class="font-display mt-2 text-4xl text-charcoal sm:text-5xl">Privacy Policy</h1>
            <p class="mt-3 text-sm text-muted">Last updated: September 13, 2026</p>
            <p class="mt-6 text-base leading-relaxed text-charcoal-soft">
                Rupnora respects your privacy. This policy explains how we collect, use, and protect information when you browse our website, create an account, place an order, or contact our support team.
            </p>

            <div class="mt-10 space-y-8 text-sm leading-relaxed text-muted sm:text-base">
                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Information We Collect</h2>
                    <p class="mt-3">We may collect your name, phone number, email address, billing and shipping address, order details, wishlist activity, and account information. When you make a payment, payment details are handled by secure payment partners; Rupnora does not store full card or UPI credentials.</p>
                    <p class="mt-3">We may also collect basic technical information such as device type, browser, pages visited, and site interactions to improve performance and shopping experience.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">How We Use Information</h2>
                    <p class="mt-3">We use your information to process orders, arrange delivery, send order updates, support returns or refunds, respond to questions, prevent misuse, improve our store, and share offers or updates when you choose to receive them.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Sharing Information</h2>
                    <p class="mt-3">We share information only when needed to operate the store, such as with payment gateways, delivery partners, customer support tools, analytics providers, and legal or regulatory authorities when required. We do not sell your personal information.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Cookies</h2>
                    <p class="mt-3">Rupnora may use cookies and similar technologies to keep your cart active, remember preferences, understand site traffic, and improve the shopping experience. You can manage cookies through your browser settings, though some features may not work properly without them.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Data Security And Retention</h2>
                    <p class="mt-3">We use reasonable technical and organisational safeguards to protect customer information. We retain order and account information for as long as needed for service, accounting, fraud prevention, legal obligations, and dispute resolution.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Your Choices</h2>
                    <p class="mt-3">You may update your account details, unsubscribe from marketing messages, or contact us to request help with privacy-related questions. Some order records may need to be retained for legal, tax, or business reasons.</p>
                </section>

                <section class="border-t border-line pt-8">
                    <h2 class="font-display text-2xl text-charcoal">Contact Us</h2>
                    <p class="mt-3">For privacy questions, please contact Rupnora through our <a href="{{ route('contact') }}" class="font-medium text-champagne-dark hover:underline">Contact Us</a> page.</p>
                </section>
            </div>
        </div>
    </section>
</x-layouts.app>

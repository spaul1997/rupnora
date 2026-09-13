@php
    $categoryLabel = $product['category_name'] ?? ucfirst(str_replace('-', ' ', $product['category']));
    $discount = $product['mrp'] > $product['price'] ? round((($product['mrp'] - $product['price']) / $product['mrp']) * 100) : 0;
    $metalOptions = collect([$product['metal'], 'Gold', 'Rose Gold', 'White Gold'])->unique()->take(3)->values();
    $bestSellersCross = $bestSellersCross ?? [];
    $gallery = collect($product['gallery'] ?? [])->filter()->take(4)->values()->all();
    $gallery = count($gallery) > 0 ? $gallery : [null, null, null, null];
    $galleryCount = count($gallery);
@endphp

<x-layouts.app :title="$product['name']" :description="$product['short_desc']">
    <div
        x-data="{
            activeImg: 0,
            lightbox: false,
            sizeGuide: false,
            zoomActive: false, zoomX: 50, zoomY: 50,
            qty: 1,
            diamondTier: 0,
            selectedMetal: {{ Illuminate\Support\Js::from($product['metal']) }},
            @if($product['purity']) selectedPurity: {{ Illuminate\Support\Js::from($product['purity']) }}, @endif
            @if($product['sizes']) selectedSize: {{ Illuminate\Support\Js::from($product['sizes'][0]) }}, @endif
            pin: '', pinStatus: null, pinLoading: false,
            checkPin() {
                if (!/^[1-9][0-9]{5}$/.test(this.pin)) { this.pinStatus = 'invalid'; return; }
                this.pinLoading = true; this.pinStatus = null;
                setTimeout(() => { this.pinLoading = false; this.pinStatus = 'available'; }, 700);
            }
        }"
    >
        <div class="container-luxe pt-6">
            <x-ui.breadcrumb :trail="[
                ['label' => $categoryLabel, 'url' => route('category.show', $product['category'])],
                ['label' => $product['name']],
            ]" />
        </div>

        <div class="container-luxe grid grid-cols-1 gap-10 py-8 lg:grid-cols-2 lg:gap-14">

            {{-- Gallery --}}
            <div>
                <div class="relative overflow-hidden rounded-2xl border border-line">
                    <div
                        class="relative aspect-square cursor-zoom-in overflow-hidden"
                        @mousemove="zoomActive = true; const r = $el.getBoundingClientRect(); zoomX = ((event.clientX - r.left) / r.width) * 100; zoomY = ((event.clientY - r.top) / r.height) * 100;"
                        @mouseleave="zoomActive = false"
                        @click="lightbox = true"
                    >
                        @foreach ($gallery as $i => $image)
                            <div x-show="activeImg === {{ $i }}" x-cloak class="absolute inset-0 transition-transform duration-200" :style="zoomActive ? `transform: scale(1.8); transform-origin: ${zoomX}% ${zoomY}%;` : ''">
                                @if ($image)
                                    <x-ui.optimized-image :src="$image" :alt="$product['name']" sizes="(min-width: 1024px) 50vw, 100vw" loading="{{ $i === 0 ? 'eager' : 'lazy' }}" fetchpriority="{{ $i === 0 ? 'high' : null }}" class="h-full w-full object-cover" />
                                @else
                                    <x-ui.product-art :art="$product['art']" :tone="$i % 2 === 0 ? 1 : 2" class="aspect-auto h-full" />
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <button @click.stop="lightbox = true" class="absolute bottom-3 right-3 flex h-9 w-9 items-center justify-center rounded-full bg-paper/90 text-charcoal shadow-card">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 9V5a1 1 0 011-1h4M20 9V5a1 1 0 00-1-1h-4M4 15v4a1 1 0 001 1h4M20 15v4a1 1 0 01-1 1h-4" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </button>
                    <button @click.stop="activeImg = (activeImg + {{ $galleryCount - 1 }}) % {{ $galleryCount }}" class="absolute left-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-paper/90 text-charcoal shadow-card">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </button>
                    <button @click.stop="activeImg = (activeImg + 1) % {{ $galleryCount }}" class="absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-paper/90 text-charcoal shadow-card">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-4 gap-3">
                    @foreach ($gallery as $i => $image)
                        <button @click="activeImg = {{ $i }}" class="overflow-hidden rounded-xl border-2 transition-colors" :class="activeImg === {{ $i }} ? 'border-champagne-dark' : 'border-line'">
                            @if ($image)
                                <x-ui.optimized-image :src="$image" :alt="$product['name']" sizes="120px" class="aspect-square w-full object-cover" />
                            @else
                                <x-ui.product-art :art="$product['art']" :tone="$i % 2 === 0 ? 1 : 2" />
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Info --}}
            <div>
                <p class="text-xs uppercase tracking-wider text-muted">{{ $categoryLabel }}</p>
                <div class="mt-1.5 flex items-start justify-between gap-4">
                    <h1 class="font-display text-[28px] leading-tight text-charcoal sm:text-[32px]">{{ $product['name'] }}</h1>
                    <div class="flex flex-shrink-0 items-center gap-1">
                        <x-ui.wishlist-button :id="$product['id']" />
                        <button class="icon-btn" aria-label="Share" @click="navigator.share ? navigator.share({title: {{ Illuminate\Support\Js::from($product['name']) }}, url: window.location.href}) : $store.ui.notify('Link copied to clipboard')">
                            <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="18" cy="5" r="2.5" /><circle cx="6" cy="12" r="2.5" /><circle cx="18" cy="19" r="2.5" /><path d="M8.2 10.8l7.6-4.6M8.2 13.2l7.6 4.6" /></svg>
                        </button>
                    </div>
                </div>
                <p class="mt-1 text-xs text-muted-light">SKU: {{ $product['sku'] }}</p>

                <div class="mt-3">
                    <x-ui.rating :value="$product['rating']" :count="$product['reviews_count']" size="lg" />
                </div>

                <div class="mt-5 rounded-xl bg-ivory-soft p-4">
                    <x-ui.price :price="$product['price']" :mrp="$product['mrp']" size="lg" />
                    <p class="mt-1 text-xs text-muted">Inclusive of all taxes</p>
                    @if ($discount > 0)
                        <p class="mt-1 text-xs font-medium text-success">You save ₹{{ number_format($product['mrp'] - $product['price']) }} ({{ $discount }}% OFF)</p>
                    @endif
                </div>

                {{-- Metal --}}
                <div class="mt-6">
                    <p class="label-luxe">Metal</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($metalOptions as $metal)
                            <button @click="selectedMetal = {{ Illuminate\Support\Js::from($metal) }}" class="rounded-full border px-4 py-2 text-sm transition-colors" :class="selectedMetal === {{ Illuminate\Support\Js::from($metal) }} ? 'border-charcoal bg-charcoal text-ivory' : 'border-line text-charcoal hover:border-charcoal/40'">{{ $metal }}</button>
                        @endforeach
                    </div>
                </div>

                @if ($product['purity'])
                    <div class="mt-5">
                        <p class="label-luxe">Gold Purity</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['14K', '18K', '22K', '24K'] as $purity)
                                <button @click="selectedPurity = {{ Illuminate\Support\Js::from($purity) }}" class="rounded-full border px-4 py-2 text-sm transition-colors" :class="selectedPurity === {{ Illuminate\Support\Js::from($purity) }} ? 'border-charcoal bg-charcoal text-ivory' : 'border-line text-charcoal hover:border-charcoal/40'">{{ $purity }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($product['diamond'])
                    <div class="mt-5">
                        <p class="label-luxe">Diamond Quality</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach (["{$product['diamond']['colour']} / {$product['diamond']['clarity']}", 'VS-FG / VS2', 'SI-GH / SI1'] as $i => $tier)
                                <button @click="diamondTier = {{ $i }}" class="rounded-full border px-4 py-2 text-xs transition-colors" :class="diamondTier === {{ $i }} ? 'border-charcoal bg-charcoal text-ivory' : 'border-line text-charcoal hover:border-charcoal/40'">{{ $tier }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($product['sizes'])
                    <div class="mt-5">
                        <div class="flex items-center justify-between">
                            <p class="label-luxe !mb-0">{{ str_contains($product['category'], 'ring') || $product['category'] === 'rings' ? 'Ring Size' : 'Size' }}</p>
                            <button @click="sizeGuide = true" class="text-xs font-medium text-champagne-dark hover:underline">Size Guide</button>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($product['sizes'] as $size)
                                <button @click="selectedSize = {{ Illuminate\Support\Js::from($size) }}" class="min-w-11 rounded-full border px-3.5 py-2 text-sm transition-colors" :class="selectedSize === {{ Illuminate\Support\Js::from($size) }} ? 'border-charcoal bg-charcoal text-ivory' : 'border-line text-charcoal hover:border-charcoal/40'">{{ $size }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-6">
                    <p class="label-luxe">Quantity</p>
                    <x-ui.quantity-selector model="qty" :max="5" />
                </div>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <button @click="$store.ui.addToCart({{ Illuminate\Support\Js::from($product['name']) }})" class="btn-primary flex-1">Add to Cart</button>
                    <a href="{{ route('checkout') }}" class="btn-secondary flex-1">Buy Now</a>
                </div>

                {{-- Delivery check --}}
                <div class="mt-7 rounded-xl border border-line p-4">
                    <p class="text-sm font-medium text-charcoal">Check Delivery Availability</p>
                    <div class="mt-2.5 flex gap-2">
                        <input type="text" x-model="pin" maxlength="6" placeholder="Enter PIN code" class="input-luxe !py-2.5 text-sm" @keydown.enter="checkPin()">
                        <button @click="checkPin()" class="btn-secondary flex-shrink-0 !px-5 !py-2.5 text-[11px]" :disabled="pinLoading">
                            <span x-show="!pinLoading">Check</span>
                            <span x-show="pinLoading" x-cloak>...</span>
                        </button>
                    </div>
                    <p x-show="pinStatus === 'invalid'" x-cloak class="mt-2 text-xs text-error">Please enter a valid 6-digit PIN code.</p>
                    <p x-show="pinStatus === 'available'" x-cloak class="mt-2 flex items-center gap-1.5 text-xs text-success">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        Delivery available. Estimated by <strong>{{ now()->addDays(6)->format('d M Y') }}</strong>.
                    </p>
                </div>

                {{-- Trust badges --}}
                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ([
                        ['label' => 'Certified Jewellery', 'icon' => 'M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['label' => 'Secure Payment', 'icon' => 'M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6l8-4z'],
                        ['label' => 'Easy Return', 'icon' => 'M4 4v6c0 5 3.5 8.5 8 10 4.5-1.5 8-5 8-10V4'],
                        ['label' => 'Insured Shipping', 'icon' => 'M3 7h13v10H3zM16 10h3l2 3v4h-5z'],
                    ] as $badge)
                        <div class="flex flex-col items-center gap-2 rounded-xl border border-line p-3 text-center">
                            <svg class="h-5 w-5 text-champagne-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="{{ $badge['icon'] }}" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            <span class="text-[11px] leading-tight text-muted">{{ $badge['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Info accordion --}}
        <div class="container-luxe pb-16">
            <div class="mx-auto max-w-3xl divide-y divide-line rounded-2xl border border-line" x-data="{ open: 'description' }">
                @php
                    $sections = [
                        'description' => ['label' => 'Description', 'content' => 'text'],
                        'details' => ['label' => 'Product Details', 'content' => 'details'],
                        'metal' => ['label' => 'Metal Details', 'content' => 'metal'],
                        'diamond' => ['label' => 'Diamond Details', 'content' => 'diamond', 'show' => (bool) $product['diamond']],
                        'dimensions' => ['label' => 'Dimensions', 'content' => 'dimensions'],
                        'care' => ['label' => 'Care Instructions', 'content' => 'care'],
                        'shipping' => ['label' => 'Shipping & Returns', 'content' => 'shipping'],
                        'certification' => ['label' => 'Certification', 'content' => 'certification'],
                    ];
                @endphp
                @foreach ($sections as $key => $section)
                    @continue(isset($section['show']) && ! $section['show'])
                    <div>
                        <button @click="open = open === '{{ $key }}' ? null : '{{ $key }}'" class="flex w-full items-center justify-between px-5 py-4 text-left sm:px-6">
                            <span class="font-display text-base text-charcoal">{{ $section['label'] }}</span>
                            <svg class="h-4 w-4 flex-shrink-0 text-muted transition-transform" :class="open === '{{ $key }}' && 'rotate-45'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round" /></svg>
                        </button>
                        <div x-cloak x-show="open === '{{ $key }}'" x-collapse class="px-5 pb-5 text-sm leading-relaxed text-muted sm:px-6">
                            @switch($section['content'])
                                @case('text')
                                    <div class="space-y-3 text-sm leading-relaxed text-muted [&_a]:text-champagne-dark [&_a]:underline [&_blockquote]:border-l-2 [&_blockquote]:border-champagne [&_blockquote]:pl-3 [&_ol]:list-decimal [&_ol]:pl-5 [&_strong]:text-charcoal [&_ul]:list-disc [&_ul]:pl-5">
                                        {!! $product['description'] !!}
                                    </div>
                                    @break
                                @case('details')
                                    <ul class="space-y-1.5">
                                        <li>Type: <span class="text-charcoal">{{ $product['type'] }}</span></li>
                                        <li>Gender: <span class="text-charcoal">{{ $product['gender'] }}</span></li>
                                        <li>SKU: <span class="text-charcoal">{{ $product['sku'] }}</span></li>
                                        <li>Occasion: <span class="text-charcoal">{{ collect($product['occasion'])->map(fn($o) => ucfirst($o))->implode(', ') }}</span></li>
                                    </ul>
                                    @break
                                @case('metal')
                                    <ul class="space-y-1.5">
                                        <li>Metal: <span class="text-charcoal">{{ $product['metal'] }}@if($product['purity']) &middot; {{ $product['purity'] }}@endif</span></li>
                                        <li>Gross Weight: <span class="text-charcoal">{{ $product['weight']['gross'] }}</span></li>
                                        <li>Net Weight: <span class="text-charcoal">{{ $product['weight']['net'] }}</span></li>
                                        <li>Stone Weight: <span class="text-charcoal">{{ $product['weight']['stone'] }}</span></li>
                                    </ul>
                                    @break
                                @case('diamond')
                                    @if ($product['diamond'])
                                        <ul class="space-y-1.5">
                                            <li>Diamond Carat: <span class="text-charcoal">{{ $product['diamond']['carat'] }}</span></li>
                                            <li>Diamond Colour: <span class="text-charcoal">{{ $product['diamond']['colour'] }}</span></li>
                                            <li>Diamond Clarity: <span class="text-charcoal">{{ $product['diamond']['clarity'] }}</span></li>
                                            <li>Diamond Shape: <span class="text-charcoal">{{ $product['diamond']['shape'] }}</span></li>
                                        </ul>
                                    @endif
                                    @break
                                @case('dimensions')
                                    Dimensions vary slightly by size selection. Standard fitting measurements are available in our Size Guide, and our stylists are happy to help you confirm fit before you order.
                                    @break
                                @case('care')
                                    Store separately in a soft pouch away from moisture and direct sunlight. Avoid contact with perfume, lotion and chlorinated water. Clean gently with a soft lint-free cloth after each wear.
                                    @break
                                @case('shipping')
                                    Free insured shipping on orders above ₹2,999. Orders are dispatched within 24&ndash;48 hours and typically arrive within 4&ndash;7 business days. Return requests must be made within 3 days from delivery as per our <a href="{{ route('refund-policy') }}" class="font-medium text-champagne-dark hover:underline">Refund Policy</a>.
                                    @break
                                @case('certification')
                                    This piece is accompanied by a certificate of authenticity{{ $product['diamond'] ? ' and an IGI diamond certification' : '' }}, along with BIS hallmarking for gold purity where applicable.
                                    @break
                            @endswitch
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Related Products --}}
        @if (count($related) > 0)
            <section class="section-pad border-t border-line">
                <div class="container-luxe">
                    <h2 class="font-display mb-8 text-2xl text-charcoal sm:text-3xl">Related Products</h2>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5">
                        @foreach ($related as $p)
                            <x-ui.product-card :product="$p" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- You May Also Like --}}
        <section class="section-pad bg-ivory-soft">
            <div class="container-luxe">
                <h2 class="font-display mb-8 text-2xl text-charcoal sm:text-3xl">You May Also Like</h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5">
                    @foreach ($bestSellersCross as $p)
                        <x-ui.product-card :product="$p" />
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Recently Viewed --}}
        <section class="section-pad">
            <div class="container-luxe">
                <h2 class="font-display mb-8 text-2xl text-charcoal sm:text-3xl">Recently Viewed</h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5">
                    @foreach ($recentlyViewed as $p)
                        <x-ui.product-card :product="$p" />
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Customer Reviews --}}
        <section class="section-pad border-t border-line bg-ivory-soft">
            <div class="container-luxe">
                <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="font-display text-2xl text-charcoal sm:text-3xl">Customer Reviews</h2>
                        <div class="mt-2"><x-ui.rating :value="$product['rating']" :count="$product['reviews_count']" /></div>
                    </div>
                    <button class="btn-secondary">Write a Review</button>
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach (array_slice($reviews, 0, 6) as $review)
                        <x-ui.review-card :review="$review" />
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Lightbox --}}
        <div x-cloak x-show="lightbox" x-transition.opacity class="fixed inset-0 z-[110] flex items-center justify-center bg-charcoal/90 p-4" @click.self="lightbox = false" @keydown.window.escape="lightbox = false">
            <button @click="lightbox = false" class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-full bg-ivory/10 text-ivory"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg></button>
            <button @click="activeImg = (activeImg + {{ $galleryCount - 1 }}) % {{ $galleryCount }}" class="absolute left-5 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-ivory/10 text-ivory"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" /></svg></button>
            <button @click="activeImg = (activeImg + 1) % {{ $galleryCount }}" class="absolute right-5 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-ivory/10 text-ivory"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg></button>
            <div class="w-full max-w-xl">
                @foreach ($gallery as $i => $image)
                    <div x-show="activeImg === {{ $i }}" x-cloak>
                        @if ($image)
                            <x-ui.optimized-image :src="$image" :alt="$product['name']" sizes="(min-width: 640px) 576px, 100vw" class="aspect-square rounded-2xl object-cover" />
                        @else
                            <x-ui.product-art :art="$product['art']" :tone="$i % 2 === 0 ? 1 : 2" class="aspect-square rounded-2xl" />
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Size Guide modal --}}
        @if ($product['sizes'])
            <div x-cloak x-show="sizeGuide" x-transition.opacity class="fixed inset-0 z-[110] flex items-center justify-center bg-charcoal/50 p-4" @click.self="sizeGuide = false">
                <div class="w-full max-w-md rounded-2xl bg-paper p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-display text-lg text-charcoal">Size Guide</h3>
                        <button @click="sizeGuide = false" class="icon-btn"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg></button>
                    </div>
                    <p class="mb-4 text-sm text-muted">Measure an existing ring's inner diameter (mm) and match it to the closest size below.</p>
                    <div class="overflow-hidden rounded-lg border border-line">
                        <table class="w-full text-sm">
                            <thead class="bg-ivory-soft text-xs uppercase text-muted">
                                <tr><th class="px-3 py-2 text-left">Size</th><th class="px-3 py-2 text-left">Diameter (mm)</th><th class="px-3 py-2 text-left">Circumference (mm)</th></tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ([['10','16.5','51.9'],['12','17.3','54.4'],['14','18.2','57.2'],['16','19.0','59.7'],['18','19.8','62.1']] as $row)
                                    <tr><td class="px-3 py-2">{{ $row[0] }}</td><td class="px-3 py-2">{{ $row[1] }}</td><td class="px-3 py-2">{{ $row[2] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Mobile sticky CTA --}}
        <div class="h-20 lg:hidden" aria-hidden="true"></div>
        <div class="fixed inset-x-0 bottom-16 z-30 flex gap-3 border-t border-line bg-paper p-3 lg:hidden" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
            <button @click="$store.ui.addToCart({{ Illuminate\Support\Js::from($product['name']) }})" class="btn-primary flex-1 !py-3">Add to Cart</button>
            <a href="{{ route('checkout') }}" class="btn-secondary flex-1 !py-3 text-center">Buy Now</a>
        </div>
    </div>
</x-layouts.app>

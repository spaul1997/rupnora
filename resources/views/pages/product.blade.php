@php
    $categoryLabel = $product['category_name'] ?? ucfirst(str_replace('-', ' ', $product['category']));
    $discount = $product['mrp'] > $product['price'] ? round((($product['mrp'] - $product['price']) / $product['mrp']) * 100) : 0;
    $metalOptions = collect($product['metal_options'] ?? [])->filter()->unique()->values();
    $selectedMetal = $metalOptions->first() ?? $product['metal'];
    $bestSellersCross = $bestSellersCross ?? [];
    $gallery = collect($product['gallery'] ?? [])->filter()->take(4)->values()->all();
    $gallery = count($gallery) > 0 ? $gallery : [null, null, null, null];
    $galleryCount = count($gallery);
    $shareUrl = route('product.show', $product['slug'] ?? $product['id']);
    $productUrlKey = $product['slug'] ?? $product['id'];
    $whatsappShareText = rawurlencode($product['name'].' - '.$shareUrl);
    $sizeOptions = collect($product['sizes'] ?? [])->filter()->values();
    $variantRows = collect($product['variants'] ?? [])
        ->filter(fn ($variant) => collect($variant)->except(['stock_quantity', 'status'])->filter()->isNotEmpty())
        ->values();
    $formatProductValue = function ($value, string $fallback = 'Not specified') {
        if (is_array($value)) {
            $value = collect($value)->filter()->implode(', ');
        }

        return filled($value) ? $value : $fallback;
    };
    $formatStockStatus = fn ($value) => $value ? ucfirst(str_replace('_', ' ', $value)) : 'Not specified';
    $cartPayload = [
        'id' => $product['id'],
        'name' => $product['name'],
        'url' => route('cart.store'),
    ];
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
            selectedMetal: {{ Illuminate\Support\Js::from($selectedMetal) }},
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

        <div class="container-luxe grid grid-cols-1 gap-10 py-8 lg:grid-cols-2 lg:items-start lg:gap-14">

            {{-- Gallery --}}
            <div class="lg:sticky lg:top-24 lg:self-start">
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
            <div class="product-details-scrollbar lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto lg:pr-2">
                <p class="text-xs uppercase tracking-wider text-muted">{{ $categoryLabel }}</p>
                <div class="mt-1.5 flex items-start justify-between gap-4">
                    <h1 class="font-display text-[28px] leading-tight text-charcoal sm:text-[32px]">{{ $product['name'] }}</h1>
                    <div class="flex flex-shrink-0 items-center gap-1">
                        <x-ui.wishlist-button :id="$productUrlKey" />
                        <a href="https://wa.me/?text={{ $whatsappShareText }}" target="_blank" rel="noopener noreferrer" class="icon-btn text-success" aria-label="Share on WhatsApp">
                            <svg class="h-5 w-5" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                                <path d="M16.04 3.2c-7.06 0-12.8 5.72-12.8 12.77 0 2.25.59 4.45 1.71 6.39L3.14 29l6.8-1.78a12.75 12.75 0 006.1 1.55h.01c7.05 0 12.79-5.73 12.79-12.78 0-3.41-1.33-6.62-3.75-9.03a12.68 12.68 0 00-9.05-3.76zm0 23.41h-.01c-1.94 0-3.84-.52-5.5-1.5l-.39-.23-4.03 1.06 1.08-3.93-.26-.4a10.56 10.56 0 01-1.62-5.64c0-5.9 4.81-10.69 10.73-10.69 2.86 0 5.55 1.11 7.57 3.14a10.62 10.62 0 013.14 7.57c0 5.9-4.81 10.7-10.71 10.7zm5.87-8.01c-.32-.16-1.9-.94-2.19-1.04-.29-.11-.5-.16-.72.16-.21.32-.83 1.04-1.02 1.25-.19.21-.38.24-.7.08-.32-.16-1.36-.5-2.59-1.6-.96-.85-1.61-1.91-1.8-2.23-.19-.32-.02-.49.14-.65.14-.14.32-.38.48-.57.16-.19.21-.32.32-.54.11-.21.05-.4-.03-.56-.08-.16-.72-1.73-.98-2.37-.26-.62-.52-.54-.72-.55h-.61c-.21 0-.56.08-.85.4-.29.32-1.12 1.09-1.12 2.66s1.15 3.09 1.31 3.3c.16.21 2.26 3.45 5.48 4.84.77.33 1.36.53 1.83.68.77.24 1.47.21 2.02.13.62-.09 1.9-.78 2.17-1.53.27-.75.27-1.39.19-1.53-.08-.13-.29-.21-.61-.37z" />
                            </svg>
                        </a>
                        <button class="icon-btn" aria-label="Share" @click="navigator.share ? navigator.share({title: {{ Illuminate\Support\Js::from($product['name']) }}, url: window.location.href}) : $store.ui.notify('Link copied to clipboard')">
                            <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="18" cy="5" r="2.5" /><circle cx="6" cy="12" r="2.5" /><circle cx="18" cy="19" r="2.5" /><path d="M8.2 10.8l7.6-4.6M8.2 13.2l7.6 4.6" /></svg>
                        </button>
                    </div>
                </div>

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

                @if ($metalOptions->isNotEmpty())
                    {{-- Metal --}}
                    <div class="mt-6">
                        <p class="label-luxe">Metal</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($metalOptions as $metal)
                                <button @click="selectedMetal = {{ Illuminate\Support\Js::from($metal) }}" class="rounded-full border px-4 py-2 text-sm transition-colors" :class="selectedMetal === {{ Illuminate\Support\Js::from($metal) }} ? 'border-charcoal bg-charcoal text-ivory' : 'border-line text-charcoal hover:border-charcoal/40'">{{ $metal }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif

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

                <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-end">
                    <div class="lg:w-36 lg:flex-shrink-0">
                        <p class="label-luxe">Quantity</p>
                        <x-ui.quantity-selector model="qty" :max="5" />
                    </div>

                    {{-- Delivery check --}}
                    <div class="rounded-xl border border-line px-4 py-3 lg:flex-1">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <p class="shrink-0 text-sm font-medium text-charcoal">Delivery Availability</p>
                            <input type="text" x-model="pin" maxlength="6" placeholder="Enter PIN code" class="input-luxe !py-2 text-sm" @keydown.enter="checkPin()">
                            <button @click="checkPin()" class="btn-secondary flex-shrink-0 !px-5 !py-2 text-[11px]" :disabled="pinLoading">
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
                </div>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <button @click="$store.ui.addToCart({ ...{{ Illuminate\Support\Js::from($cartPayload) }}, qty, size: typeof selectedSize !== 'undefined' ? selectedSize : null })" class="btn-primary flex-1">Add to Cart</button>
                    <a href="{{ route('checkout') }}" class="btn-secondary flex-1">Buy Now</a>
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

                {{-- Info accordion --}}
                <div class="mt-6 divide-y divide-line rounded-2xl border border-line" x-data="{ open: 'description' }">
                    @php
                        $sections = [
                            'description' => ['label' => 'Description', 'content' => 'text'],
                            'details' => ['label' => 'Product Details', 'content' => 'details'],
                            'metal' => ['label' => 'Metal Details', 'content' => 'metal'],
                            'diamond' => ['label' => 'Diamond Details', 'content' => 'diamond', 'show' => (bool) $product['diamond']],
                            'gemstone' => ['label' => 'Gemstone Details', 'content' => 'gemstone', 'show' => (bool) ($product['gemstone'] ?? null)],
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
                                        <dl class="grid grid-cols-1 gap-x-5 gap-y-3 sm:grid-cols-2">
                                            @foreach ([
                                                'Product Name' => $product['name'],
                                                'SKU' => $product['sku'],
                                                'Barcode' => $product['barcode'] ?? null,
                                                'Brand' => $product['brand'] ?? null,
                                                'Category' => $product['category_name'],
                                                'Collection' => $product['collection_names'] ?? [],
                                                'Jewellery Type' => $product['type'],
                                                'Occasion' => collect($product['occasion'])->map(fn($occasion) => ucfirst($occasion))->all(),
                                                'Gender' => $product['gender'],
                                                'Stock Status' => $formatStockStatus($product['stock_status'] ?? null),
                                                'Available Quantity' => $product['stock_quantity'] ?? null,
                                            ] as $label => $value)
                                                <div>
                                                    <dt class="text-xs uppercase tracking-wide text-muted">{{ $label }}</dt>
                                                    <dd class="mt-0.5 font-medium text-charcoal">{{ $formatProductValue($value) }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                        @break
                                    @case('metal')
                                        <dl class="grid grid-cols-1 gap-x-5 gap-y-3 sm:grid-cols-2">
                                            @foreach ([
                                                'Material' => $product['metal'],
                                                'Finish / Plating' => $product['finish_plating'] ?? null,
                                                'Colour' => $product['colour'] ?? null,
                                                'Purity' => $product['purity'] ?? null,
                                                'Stone Type' => $product['stone_type'] ?? null,
                                                'Stone Colour' => $product['stone_colour'] ?? null,
                                                'Gross Weight' => $product['weight']['gross'],
                                                'Net Weight' => $product['weight']['net'],
                                                'Metal Weight' => $product['weight']['metal'],
                                                'Stone Weight' => $product['weight']['stone'],
                                                'Adjustable' => $product['is_adjustable'] ? 'Yes' : 'No',
                                                'Water Resistant' => $product['is_water_resistant'] ? 'Yes' : 'No',
                                                'Return Available' => $product['is_return_available'] ? 'Yes' : 'No',
                                                'Refund Available' => $product['is_refund_available'] ? 'Yes' : 'No',
                                            ] as $label => $value)
                                                <div>
                                                    <dt class="text-xs uppercase tracking-wide text-muted">{{ $label }}</dt>
                                                    <dd class="mt-0.5 font-medium text-charcoal">{{ $formatProductValue($value) }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                        @break
                                    @case('diamond')
                                        @if ($product['diamond'])
                                            <dl class="grid grid-cols-1 gap-x-5 gap-y-3 sm:grid-cols-2">
                                                @foreach ([
                                                    'Diamond Carat' => $product['diamond']['carat'] ?? null,
                                                    'Diamond Colour' => $product['diamond']['colour'] ?? null,
                                                    'Diamond Clarity' => $product['diamond']['clarity'] ?? null,
                                                    'Diamond Cut' => $product['diamond']['cut'] ?? null,
                                                    'Diamond Shape' => $product['diamond']['shape'] ?? null,
                                                    'Diamond Count' => $product['diamond']['count'] ?? null,
                                                ] as $label => $value)
                                                    <div>
                                                        <dt class="text-xs uppercase tracking-wide text-muted">{{ $label }}</dt>
                                                        <dd class="mt-0.5 font-medium text-charcoal">{{ $formatProductValue($value) }}</dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        @endif
                                        @break
                                    @case('gemstone')
                                        @if ($product['gemstone'])
                                            <dl class="grid grid-cols-1 gap-x-5 gap-y-3 sm:grid-cols-2">
                                                @foreach ([
                                                    'Gemstone Type' => $product['gemstone']['type'] ?? null,
                                                    'Gemstone Colour' => $product['gemstone']['colour'] ?? null,
                                                    'Gemstone Weight' => $product['gemstone']['weight'] ?? null,
                                                ] as $label => $value)
                                                    <div>
                                                        <dt class="text-xs uppercase tracking-wide text-muted">{{ $label }}</dt>
                                                        <dd class="mt-0.5 font-medium text-charcoal">{{ $formatProductValue($value) }}</dd>
                                                    </div>
                                                @endforeach
                                            </dl>
                                        @endif
                                        @break
                                    @case('dimensions')
                                        <div class="space-y-4">
                                            <dl class="grid grid-cols-1 gap-x-5 gap-y-3 sm:grid-cols-2">
                                                @foreach ([
                                                    'Available Sizes' => $sizeOptions->all(),
                                                    'Adjustable' => ($product['is_adjustable'] ?? false) ? 'Yes' : 'No',
                                                    'Gross Weight' => $product['weight']['gross'] ?? null,
                                                    'Net Weight' => $product['weight']['net'] ?? null,
                                                    'Metal Weight' => $product['weight']['metal'] ?? null,
                                                    'Stone Weight' => $product['weight']['stone'] ?? null,
                                                ] as $label => $value)
                                                    <div>
                                                        <dt class="text-xs uppercase tracking-wide text-muted">{{ $label }}</dt>
                                                        <dd class="mt-0.5 font-medium text-charcoal">{{ $formatProductValue($value) }}</dd>
                                                    </div>
                                                @endforeach
                                            </dl>

                                            @if ($variantRows->isNotEmpty())
                                                <div class="overflow-x-auto rounded-xl border border-line">
                                                    <table class="w-full min-w-[520px] text-left text-xs">
                                                        <thead class="bg-ivory-soft text-muted">
                                                            <tr>
                                                                <th class="px-3 py-2 font-medium">SKU</th>
                                                                <th class="px-3 py-2 font-medium">Size</th>
                                                                <th class="px-3 py-2 font-medium">Metal</th>
                                                                <th class="px-3 py-2 font-medium">Colour</th>
                                                                <th class="px-3 py-2 font-medium">Stock</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-line">
                                                            @foreach ($variantRows as $variant)
                                                                <tr>
                                                                    <td class="px-3 py-2 text-charcoal">{{ $formatProductValue($variant['sku'] ?? null, '—') }}</td>
                                                                    <td class="px-3 py-2 text-charcoal">{{ $formatProductValue($variant['size'] ?? null, '—') }}</td>
                                                                    <td class="px-3 py-2 text-charcoal">{{ $formatProductValue($variant['metal'] ?? null, '—') }}@if(! empty($variant['purity'])) · {{ $variant['purity'] }}@endif</td>
                                                                    <td class="px-3 py-2 text-charcoal">{{ $formatProductValue($variant['colour'] ?? null, '—') }}</td>
                                                                    <td class="px-3 py-2 text-charcoal">{{ $formatProductValue($variant['stock_quantity'] ?? null, '—') }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                        @break
                                    @case('care')
                                        <ul class="list-disc space-y-2 pl-5">
                                            <li>Store this {{ $formatProductValue($product['metal'] ?? null, 'jewellery') }} piece separately in a soft pouch to avoid scratches.</li>
                                            @if (! empty($product['finish_plating']))
                                                <li>Protect the {{ $product['finish_plating'] }} finish from perfume, lotion and harsh cleaners.</li>
                                            @endif
                                            <li>{{ ($product['is_water_resistant'] ?? false) ? 'Light water contact is acceptable, but dry the piece gently after wear.' : 'Keep away from moisture and chlorinated water to preserve the finish.' }}</li>
                                            @if (($product['has_diamond'] ?? false) || ($product['has_gemstone'] ?? false))
                                                <li>Clean stones gently with a soft lint-free cloth; avoid abrasive brushes.</li>
                                            @endif
                                        </ul>
                                        @break
                                    @case('shipping')
                                        <ul class="list-disc space-y-2 pl-5">
                                            <li>Stock status: <span class="font-medium text-charcoal">{{ $formatStockStatus($product['stock_status'] ?? null) }}</span>.</li>
                                            <li>{{ ($product['in_stock'] ?? false) ? 'Orders are dispatched after confirmation and usually arrive within 4–7 business days.' : 'This product is currently out of stock. Delivery will be available once stock is updated.' }}</li>
                                            <li>Return: <span class="font-medium text-charcoal">{{ ($product['is_return_available'] ?? false) ? 'Available' : 'Not available' }}</span>.</li>
                                            <li>Refund: <span class="font-medium text-charcoal">{{ ($product['is_refund_available'] ?? false) ? 'Available' : 'Not available' }}</span>. See our <a href="{{ route('refund-policy') }}" class="font-medium text-champagne-dark hover:underline">Refund Policy</a>.</li>
                                        </ul>
                                        @break
                                    @case('certification')
                                        <dl class="grid grid-cols-1 gap-x-5 gap-y-3 sm:grid-cols-2">
                                            @foreach ([
                                                'SKU' => $product['sku'] ?? null,
                                                'Barcode' => $product['barcode'] ?? null,
                                                'Material' => $product['metal'] ?? null,
                                                'Purity' => $product['purity'] ?? null,
                                                'Diamond Certified' => ($product['has_diamond'] ?? false) ? 'Yes' : 'No',
                                                'Gemstone Included' => ($product['has_gemstone'] ?? false) ? 'Yes' : 'No',
                                            ] as $label => $value)
                                                <div>
                                                    <dt class="text-xs uppercase tracking-wide text-muted">{{ $label }}</dt>
                                                    <dd class="mt-0.5 font-medium text-charcoal">{{ $formatProductValue($value) }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                        @break
                                @endswitch
                            </div>
                        </div>
                    @endforeach
                </div>
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
            <button @click="$store.ui.addToCart({ ...{{ Illuminate\Support\Js::from($cartPayload) }}, qty, size: typeof selectedSize !== 'undefined' ? selectedSize : null })" class="btn-primary flex-1 !py-3">Add to Cart</button>
            <a href="{{ route('checkout') }}" class="btn-secondary flex-1 !py-3 text-center">Buy Now</a>
        </div>
    </div>
</x-layouts.app>

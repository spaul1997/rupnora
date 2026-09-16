@php
    $wishlistStateProducts = collect($products)->map(function ($product) {
        $productKey = (string) ($product['slug'] ?? $product['id']);

        return [
            'id' => $productKey,
            'name' => $product['name'],
            'removeUrl' => route('account.wishlist.destroy', $productKey),
        ];
    })->values()->all();
@endphp

<x-layouts.app :title="$title">
    <x-account.shell active="wishlist">
        <div x-data="wishlistPage({{ Illuminate\Support\Js::from($wishlistStateProducts) }})">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="font-display text-2xl text-charcoal sm:text-3xl">Wishlist</h1>
            <span class="text-sm text-muted" x-text="count === 1 ? '1 item' : `${count} items`">{{ count($products) }} items</span>
        </div>

        <div x-show="!hasProducts" @if(count($products) > 0) x-cloak @endif>
            <x-ui.empty-state icon="heart" title="Your wishlist is empty" description="Save pieces you love to find them here later." action-label="Explore Jewellery" :action-url="route('home')" />
        </div>

        <div x-show="hasProducts" @if(count($products) === 0) x-cloak @endif>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-5">
                @foreach ($products as $product)
                    @php
                        $productUrlKey = $product['slug'] ?? $product['id'];
                        $cartPayload = [
                            'id' => $product['id'],
                            'name' => $product['name'],
                            'url' => route('cart.store'),
                        ];
                    @endphp
                    <div class="card-luxe overflow-hidden" x-show="!products[{{ $loop->index }}].removed" x-transition>
                        <a href="{{ route('product.show', $productUrlKey) }}" class="block">
                            @if (! empty($product['image']))
                                <x-ui.optimized-image :src="$product['image']" :alt="$product['name']" sizes="(min-width: 1280px) 20vw, (min-width: 1024px) 25vw, (min-width: 640px) 33vw, 50vw" class="aspect-square w-full object-cover" />
                            @else
                                <x-ui.product-art :art="$product['art']" />
                            @endif
                        </a>
                        <div class="p-2.5">
                            <p class="text-[9.5px] uppercase tracking-wide text-muted">{{ $product['category_name'] ?? ucfirst(str_replace('-', ' ', $product['category'])) }}</p>
                            <a href="{{ route('product.show', $productUrlKey) }}" class="mt-0.5 block truncate font-display text-[12.5px] text-charcoal hover:text-champagne-dark">{{ $product['name'] }}</a>
                            <div class="mt-1"><x-ui.price :price="$product['price']" :mrp="$product['mrp']" size="xs" /></div>
                            <p class="mt-1 flex items-center gap-1.5 text-[10.5px] {{ $product['in_stock'] ? 'text-success' : 'text-error' }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $product['in_stock'] ? 'bg-success' : 'bg-error' }}"></span>
                                {{ $product['in_stock'] ? 'In Stock' : 'Out of Stock' }}
                            </p>
                            <div class="mt-2 flex gap-1.5">
                                <button @click="$store.ui.addToCart({{ Illuminate\Support\Js::from($cartPayload) }})" class="btn-primary flex-1 !py-1.5 !px-2 text-[9.5px]" @if(!$product['in_stock']) disabled @endif>Add to Cart</button>
                                <button @click="removeProduct({{ $loop->index }})" :disabled="products[{{ $loop->index }}].syncing" class="icon-btn h-8 w-8 flex-shrink-0 border border-line text-muted hover:text-error disabled:opacity-40" aria-label="Remove">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-9 0h10l-1 13H8L7 7z" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        </div>
    </x-account.shell>
</x-layouts.app>

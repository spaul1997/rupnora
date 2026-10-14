@php
    $editing = isset($offer);
    $selectedCategories = collect(old('categories', $editing ? $offer->categories->pluck('id')->all() : []))->map(fn ($id) => (string) $id)->values();
    $selectedProducts = collect(old('products', $editing ? $offer->includedProducts->pluck('id')->all() : []))->map(fn ($id) => (string) $id)->values();
    $excludedProducts = collect(old('excluded_products', $editing ? $offer->excludedProducts->pluck('id')->all() : []))->map(fn ($id) => (string) $id)->values();
    $productOptions = $products->map(fn ($item) => [
        'id' => (string) $item->id,
        'name' => $item->name,
        'sku' => $item->sku,
    ])->values();
@endphp

@csrf
@if ($editing)
    @method('PUT')
@endif

<div
    x-data="{
        scopeType: {{ Illuminate\Support\Js::from(old('scope_type', $offer->scope_type ?? 'category')) }},
        selectedCategories: {{ Illuminate\Support\Js::from($selectedCategories) }},
        selectedProducts: {{ Illuminate\Support\Js::from($selectedProducts) }},
        products: {{ Illuminate\Support\Js::from($productOptions) }},
        productSearch: '',
        searchMatches(product, search) {
            const term = search.trim().toLowerCase();
            return term === '' || product.name.toLowerCase().includes(term) || product.sku.toLowerCase().includes(term);
        },
    }"
    class="grid grid-cols-1 gap-6 lg:grid-cols-3"
>
    <div class="space-y-6 lg:col-span-2">
        <div class="admin-card space-y-5 p-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Offer Details</h3>
                <p class="mt-1 text-xs text-gray-400">Describe the offer and choose the exact period when its discount should apply.</p>
            </div>
            <x-admin.form.input label="Offer Reason" name="reason" required :value="$offer->reason ?? null" maxlength="255" placeholder="e.g. Diwali sale, Clearance offer" />
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-admin.form.input label="Start Date" name="start_date" type="date" required :value="isset($offer) ? $offer->start_date?->format('Y-m-d') : today()->format('Y-m-d')" />
                <x-admin.form.input label="End Date" name="end_date" type="date" required :value="isset($offer) ? $offer->end_date?->format('Y-m-d') : null" />
            </div>
        </div>

        <div class="admin-card space-y-5 p-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Apply Discount To</h3>
                <p class="mt-1 text-xs text-gray-400">Choose category-wise discounting with optional exclusions, or select products directly.</p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <label class="flex cursor-pointer gap-3 rounded-xl border p-4 transition-colors" :class="scopeType === 'category' ? 'border-champagne-dark bg-ivory-soft' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="scope_type" value="category" x-model="scopeType" class="mt-0.5 h-4 w-4 border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                    <span><span class="block text-sm font-semibold text-gray-900">Category wise</span><span class="mt-1 block text-xs text-gray-500">Select categories, then exclude individual products if needed.</span></span>
                </label>
                <label class="flex cursor-pointer gap-3 rounded-xl border p-4 transition-colors" :class="scopeType === 'product' ? 'border-champagne-dark bg-ivory-soft' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="scope_type" value="product" x-model="scopeType" class="mt-0.5 h-4 w-4 border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                    <span><span class="block text-sm font-semibold text-gray-900">Product wise</span><span class="mt-1 block text-xs text-gray-500">Apply the discount only to selected products.</span></span>
                </label>
            </div>
            @error('scope_type')<p class="text-xs text-error">{{ $message }}</p>@enderror

            <div x-show="scopeType === 'category'" x-cloak class="space-y-5 border-t border-gray-100 pt-5">
                <div>
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">Select Categories <span class="text-error">*</span></h4>
                            <p class="mt-0.5 text-xs text-gray-400">Selecting a parent category also includes products in its subcategories.</p>
                        </div>
                        <span class="text-xs font-medium text-champagne-dark" x-text="`${selectedCategories.length} selected`"></span>
                    </div>
                    <div class="max-h-96 space-y-2 overflow-auto rounded-xl border border-gray-200 p-3">
                        <div class="grid gap-3 px-3 text-[11px] font-semibold uppercase tracking-wide text-gray-400" style="min-width: 38rem; grid-template-columns: minmax(0, 1fr) 10rem 9rem;">
                            <span>Parent Category</span>
                            <span>Discount Type</span>
                            <span>Discount Value</span>
                        </div>
                        @foreach ($categories as $category)
                            @php
                                $categoryTarget = $editing ? $offer->categories->firstWhere('id', $category->id) : null;
                                $categoryDiscountType = old("category_discounts.{$category->id}.discount_type", $categoryTarget?->pivot?->discount_type ?? 'percentage');
                                $categoryDiscountValue = old("category_discounts.{$category->id}.discount_value", $categoryTarget?->pivot?->discount_value);
                                if ($categoryDiscountType === 'percentage' && is_numeric($categoryDiscountValue) && (float) $categoryDiscountValue === floor((float) $categoryDiscountValue)) {
                                    $categoryDiscountValue = (int) $categoryDiscountValue;
                                }
                                $categorySelected = $selectedCategories->contains((string) $category->id);
                            @endphp
                            <div
                                x-data="{ discountType: {{ Illuminate\Support\Js::from($categoryDiscountType) }} }"
                                class="grid items-start gap-3 rounded-lg border px-3 py-3 transition-colors"
                                style="min-width: 38rem; grid-template-columns: minmax(0, 1fr) 10rem 9rem;"
                                :class="selectedCategories.includes('{{ $category->id }}') ? 'border-champagne-dark/40 bg-ivory-soft' : 'border-transparent hover:bg-gray-50'"
                            >
                                <label class="flex min-w-0 items-center gap-2.5 py-2 text-sm text-gray-700">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}" x-model="selectedCategories" class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                                    <span class="truncate font-medium">{{ $category->name }}</span>
                                </label>
                                <div>
                                    <label for="category_discount_type_{{ $category->id }}" class="sr-only">Discount Type</label>
                                    <select
                                        id="category_discount_type_{{ $category->id }}"
                                        name="category_discounts[{{ $category->id }}][discount_type]"
                                        class="admin-select text-sm"
                                        x-model="discountType"
                                        :disabled="!selectedCategories.includes('{{ $category->id }}')"
                                        @disabled(! $categorySelected)
                                    >
                                        <option value="percentage" @selected($categoryDiscountType === 'percentage')>Percentage</option>
                                        <option value="fixed" @selected($categoryDiscountType === 'fixed')>Fixed Amount</option>
                                    </select>
                                    @error("category_discounts.{$category->id}.discount_type")<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="category_discount_value_{{ $category->id }}" class="sr-only">Discount Value</label>
                                    <input
                                        id="category_discount_value_{{ $category->id }}"
                                        name="category_discounts[{{ $category->id }}][discount_value]"
                                        type="number"
                                        :min="discountType === 'percentage' ? '1' : '0.01'"
                                        :step="discountType === 'percentage' ? '1' : '0.01'"
                                        value="{{ $categoryDiscountValue }}"
                                        :placeholder="discountType === 'percentage' ? 'Whole %' : 'Amount'"
                                        class="admin-input text-sm"
                                        :disabled="!selectedCategories.includes('{{ $category->id }}')"
                                        @disabled(! $categorySelected)
                                    >
                                    @error("category_discounts.{$category->id}.discount_value")<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('categories')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    @error('categories.*')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    @error('category_discounts')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <div class="mb-3">
                        <h4 class="text-sm font-semibold text-gray-900">Except Products</h4>
                        <p class="mt-0.5 text-xs text-gray-400">Optional. Tick the products under each selected category that should keep their normal price.</p>
                    </div>
                    <div class="space-y-3">
                        @foreach ($categories as $category)
                            @php
                                $categoryProducts = $products->filter(fn ($product) => $product->category_id === $category->id || $product->category?->parent_id === $category->id);
                                $categoryProductOptions = $categoryProducts->map(fn ($product) => [
                                    'id' => (string) $product->id,
                                    'name' => $product->name,
                                    'sku' => $product->sku,
                                ])->values();
                                $categoryExcluded = $excludedProducts->intersect($categoryProductOptions->pluck('id'))->values();
                            @endphp
                            <div
                                x-data="{
                                    categoryId: '{{ $category->id }}',
                                    items: {{ Illuminate\Support\Js::from($categoryProductOptions) }},
                                    excluded: {{ Illuminate\Support\Js::from($categoryExcluded) }},
                                    search: '',
                                    get visibleItems() {
                                        return this.items.filter((item) => this.searchMatches(item, this.search));
                                    },
                                    get excludedItems() {
                                        return this.items.filter((item) => this.excluded.includes(item.id));
                                    },
                                    get allVisibleExcluded() {
                                        return this.visibleItems.length > 0 && this.visibleItems.every((item) => this.excluded.includes(item.id));
                                    },
                                    toggleVisible() {
                                        const visibleIds = this.visibleItems.map((item) => item.id);
                                        this.excluded = this.allVisibleExcluded
                                            ? this.excluded.filter((id) => !visibleIds.includes(id))
                                            : [...new Set([...this.excluded, ...visibleIds])];
                                    },
                                    remove(id) {
                                        this.excluded = this.excluded.filter((excludedId) => excludedId !== id);
                                    },
                                }"
                                x-show="selectedCategories.includes(categoryId)"
                                x-cloak
                                class="overflow-hidden rounded-xl border border-gray-200 bg-white"
                            >
                                <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/70 px-4 py-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-gray-800">{{ $category->name }}</p>
                                        <p class="text-[11px] text-gray-400">{{ $categoryProducts->count() }} {{ Str::plural('product', $categoryProducts->count()) }}</p>
                                    </div>
                                    <span
                                        class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold"
                                        :class="excluded.length > 0 ? 'bg-champagne-light text-champagne-dark' : 'bg-gray-100 text-gray-400'"
                                        x-text="excluded.length > 0 ? `${excluded.length} excluded` : 'None excluded'"
                                    ></span>
                                </div>

                                @if ($categoryProducts->isEmpty())
                                    <p class="px-4 py-6 text-center text-xs text-gray-400">No products are available in this category.</p>
                                @else
                                    <div class="space-y-3 p-4">
                                        <div x-show="excludedItems.length > 0" class="flex flex-wrap items-center gap-1.5">
                                            <template x-for="item in excludedItems" :key="item.id">
                                                <span class="inline-flex max-w-full items-center gap-1 rounded-full border border-champagne-dark/20 bg-ivory-soft py-1 pl-2.5 pr-1 text-xs font-medium text-champagne-dark">
                                                    <span class="truncate" x-text="item.name"></span>
                                                    <button
                                                        type="button"
                                                        class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full transition-colors hover:bg-champagne-dark hover:text-white"
                                                        :aria-label="`Remove ${item.name}`"
                                                        @click="remove(item.id)"
                                                    >
                                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
                                                    </button>
                                                </span>
                                            </template>
                                            <button type="button" class="ml-1 text-xs font-medium text-gray-400 underline-offset-2 hover:text-error hover:underline" @click="excluded = []">Clear all</button>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <div class="relative flex-1">
                                                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="M20 20l-3.5-3.5" stroke-linecap="round" /></svg>
                                                <label for="excluded_products_search_{{ $category->id }}" class="sr-only">Search {{ $category->name }} products</label>
                                                <input id="excluded_products_search_{{ $category->id }}" type="search" x-model="search" placeholder="Search name or SKU..." autocomplete="off" class="admin-input !py-2 !pl-9">
                                            </div>
                                            <button
                                                type="button"
                                                class="shrink-0 rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-600 transition-colors hover:border-champagne-dark hover:text-champagne-dark disabled:cursor-not-allowed disabled:opacity-50"
                                                :disabled="visibleItems.length === 0"
                                                @click="toggleVisible()"
                                                x-text="allVisibleExcluded ? 'Unselect all' : 'Select all'"
                                            ></button>
                                        </div>

                                        <div class="max-h-[calc(7.5rem+4px)] divide-y divide-gray-100 overflow-y-auto rounded-lg border border-gray-200" role="group" aria-label="{{ $category->name }} products to exclude">
                                            <template x-for="item in items" :key="item.id">
                                                <label
                                                    x-show="searchMatches(item, search)"
                                                    class="flex cursor-pointer items-center h-10 shrink-0 gap-3 px-3 text-sm transition-colors"
                                                    :class="excluded.includes(item.id) ? 'bg-ivory-soft' : 'hover:bg-gray-50'"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        name="excluded_products_by_category[{{ $category->id }}][]"
                                                        :value="item.id"
                                                        x-model="excluded"
                                                        :disabled="!selectedCategories.includes(categoryId)"
                                                        class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40"
                                                    >
                                                    <span class="min-w-0 flex-1 truncate font-medium" :class="excluded.includes(item.id) ? 'text-champagne-dark' : 'text-gray-700'" x-text="item.name"></span>
                                                    <span class="shrink-0 font-mono text-[11px] text-gray-400" x-text="item.sku"></span>
                                                </label>
                                            </template>
                                            <p x-show="visibleItems.length === 0" class="px-3 py-6 text-center text-xs text-gray-400">No products match your search.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        <p x-show="selectedCategories.length === 0" class="rounded-xl border border-dashed border-gray-200 px-3 py-6 text-center text-sm text-gray-400">Select a parent category to view its products.</p>
                    </div>
                    @error('excluded_products')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                    @error('excluded_products.*')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div x-show="scopeType === 'product'" x-cloak class="space-y-3 border-t border-gray-100 pt-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900">Select Products <span class="text-error">*</span></h4>
                        <p class="mt-0.5 text-xs text-gray-400">Choose products and set a separate discount for each one.</p>
                    </div>
                    <span class="text-xs font-medium text-champagne-dark" x-text="`${selectedProducts.length} selected`"></span>
                </div>
                <input type="search" x-model="productSearch" placeholder="Search product name or SKU..." class="admin-input">
                <div class="max-h-80 overflow-auto rounded-xl border border-gray-200 p-3">
                    <div class="mb-2 grid gap-3 px-3 text-[11px] font-semibold uppercase tracking-wide text-gray-400" style="min-width: 38rem; grid-template-columns: minmax(0, 1fr) 10rem 9rem;">
                        <span>Product</span>
                        <span>Discount Type</span>
                        <span>Discount Value</span>
                    </div>
                    @foreach ($products as $item)
                        @php
                            $assignedElsewhere = ($item->discount_offer_id && (! $editing || $item->discount_offer_id !== $offer->id)) || (! $item->discount_offer_id && $item->discount_type);
                            $productTarget = $editing ? $offer->includedProducts->firstWhere('id', $item->id) : null;
                            $productDiscountType = old("product_discounts.{$item->id}.discount_type", $productTarget?->pivot?->discount_type ?? 'percentage');
                            $productDiscountValue = old("product_discounts.{$item->id}.discount_value", $productTarget?->pivot?->discount_value);
                            if ($productDiscountType === 'percentage' && is_numeric($productDiscountValue) && (float) $productDiscountValue === floor((float) $productDiscountValue)) {
                                $productDiscountValue = (int) $productDiscountValue;
                            }
                            $productSelected = $selectedProducts->contains((string) $item->id);
                        @endphp
                        <div
                            x-data="{ discountType: {{ Illuminate\Support\Js::from($productDiscountType) }} }"
                            x-show="searchMatches(products.find((product) => product.id === '{{ $item->id }}'), productSearch)"
                            class="grid items-start gap-3 rounded-lg border px-3 py-3 text-sm {{ $assignedElsewhere ? 'cursor-not-allowed border-transparent bg-gray-50 text-gray-400' : 'text-gray-700' }}"
                            style="min-width: 38rem; grid-template-columns: minmax(0, 1fr) 10rem 9rem;"
                            :class="selectedProducts.includes('{{ $item->id }}') ? 'border-champagne-dark/40 bg-ivory-soft' : 'border-transparent hover:bg-gray-50'"
                        >
                            <label class="flex min-w-0 items-center gap-2.5 py-2">
                                <input type="checkbox" name="products[]" value="{{ $item->id }}" x-model="selectedProducts" @disabled($assignedElsewhere) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                                <span class="min-w-0 flex-1"><span class="block truncate font-medium">{{ $item->name }}</span><span class="block text-xs text-gray-400">{{ $item->sku }} &middot; {{ $item->category?->name ?? 'Uncategorised' }}</span></span>
                                @if ($assignedElsewhere)<span class="text-[10px] font-semibold uppercase tracking-wide text-amber-600">Other offer</span>@endif
                            </label>
                            <div>
                                <label for="product_discount_type_{{ $item->id }}" class="sr-only">Discount Type</label>
                                <select
                                    id="product_discount_type_{{ $item->id }}"
                                    name="product_discounts[{{ $item->id }}][discount_type]"
                                    class="admin-select text-sm"
                                    x-model="discountType"
                                    :disabled="!selectedProducts.includes('{{ $item->id }}')"
                                    @disabled(! $productSelected || $assignedElsewhere)
                                >
                                    <option value="percentage" @selected($productDiscountType === 'percentage')>Percentage</option>
                                    <option value="fixed" @selected($productDiscountType === 'fixed')>Fixed Amount</option>
                                </select>
                                @error("product_discounts.{$item->id}.discount_type")<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="product_discount_value_{{ $item->id }}" class="sr-only">Discount Value</label>
                                <input
                                    id="product_discount_value_{{ $item->id }}"
                                    name="product_discounts[{{ $item->id }}][discount_value]"
                                    type="number"
                                    :min="discountType === 'percentage' ? '1' : '0.01'"
                                    :step="discountType === 'percentage' ? '1' : '0.01'"
                                    value="{{ $productDiscountValue }}"
                                    :placeholder="discountType === 'percentage' ? 'Whole %' : 'Amount'"
                                    class="admin-input text-sm"
                                    :disabled="!selectedProducts.includes('{{ $item->id }}')"
                                    @disabled(! $productSelected || $assignedElsewhere)
                                >
                                @error("product_discounts.{$item->id}.discount_value")<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('products')<p class="text-xs text-error">{{ $message }}</p>@enderror
                @error('products.*')<p class="text-xs text-error">{{ $message }}</p>@enderror
                @error('product_discounts')<p class="text-xs text-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="admin-card space-y-4 p-6">
            <label class="flex items-center gap-2.5 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $offer->is_active ?? true)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                Active offer
            </label>
            <p class="text-xs leading-5 text-gray-400">An inactive offer keeps its selections but does not change product prices.</p>
        </div>
        <div class="admin-card space-y-3 p-6 text-sm text-gray-600">
            <h3 class="font-semibold text-gray-900">How pricing works</h3>
            <p>The discount starts on the start date and remains valid through the end date.</p>
            <p>Excluded products keep their normal price.</p>
            <p>Making charges and GST are included after the discount.</p>
        </div>
        <button type="submit" class="admin-btn-primary w-full">{{ $editing ? 'Update Offer' : 'Create Offer' }}</button>
        <a href="{{ route('admin.discount-offers.index') }}" class="admin-btn-secondary w-full">Cancel</a>
    </div>
</div>

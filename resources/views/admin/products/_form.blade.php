@csrf
@if (isset($product))
    @method('PUT')
@endif

@php
    $productCategory = isset($product) ? $product->category : null;
    $selectedParentCategoryId = old('parent_category_id', $productCategory?->parent_id ?? ($productCategory && ! $productCategory->parent_id ? $productCategory->id : ''));
    $selectedCategoryId = old('category_id', $productCategory?->parent_id ? $productCategory->id : '');
    $selectedJewelleryType = old('jewellery_type', $product->jewellery_type ?? '');
    $selectedCollections = \App\Models\Product::normalizeCollectionSlugs(old('collection', isset($product) ? $product->collectionSlugs() : []));
@endphp

<div
    x-data="{
        selectedParentCategory: {{ Illuminate\Support\Js::from((string) $selectedParentCategoryId) }},
        selectedCategory: {{ Illuminate\Support\Js::from((string) $selectedCategoryId) }},
        selectedJewelleryType: {{ Illuminate\Support\Js::from((string) $selectedJewelleryType) }},
        selectedCollections: {{ Illuminate\Support\Js::from(array_values($selectedCollections)) }},
        allSubcategories: {{ Illuminate\Support\Js::from($subcategories->map(fn($category) => ['id' => (string) $category->id, 'parent_id' => (string) $category->parent_id, 'name' => $category->name])->values()) }},
        allJewelleryTypes: {{ Illuminate\Support\Js::from($jewelleryTypes->map(fn($type) => ['name' => $type->name])->values()) }},
        allCollections: {{ Illuminate\Support\Js::from($collections->map(fn($collection) => ['name' => $collection->name, 'slug' => $collection->slug])->values()) }},
        hasDiamond: {{ old('has_diamond', $product->has_diamond ?? false) ? 'true' : 'false' }},
        hasGemstone: {{ old('has_gemstone', $product->has_gemstone ?? false) ? 'true' : 'false' }},
        variants: {{ Illuminate\Support\Js::from(isset($product) ? $product->variants->map(fn($v) => ['size' => $v->size, 'metal' => $v->metal, 'purity' => $v->purity, 'colour' => $v->colour, 'price' => $v->price, 'offer_price' => $v->offer_price, 'stock_quantity' => $v->stock_quantity])->values() : []) }},
        filteredSubcategories() {
            return this.allSubcategories.filter((category) => category.parent_id === this.selectedParentCategory);
        },
        addVariant() { this.variants.push({ size: '', metal: '', purity: '', colour: '', price: '', offer_price: '', stock_quantity: 0 }) },
        removeVariant(i) { this.variants.splice(i, 1) },
    }"
    class="grid grid-cols-1 gap-6 lg:grid-cols-3"
>
    @if ($errors->any())
        <div
            x-ref="validationSummary"
            x-init="$nextTick(() => { $el.focus(); $el.scrollIntoView({ behavior: 'smooth', block: 'start' }) })"
            tabindex="-1"
            role="alert"
            class="rounded-xl border border-error/30 bg-error/10 px-4 py-3 text-sm text-error outline-none lg:col-span-3"
        >
            <p class="font-semibold">The product was not saved. Please fix the following:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6 lg:col-span-2">

        {{-- Basic Information --}}
        <div class="admin-card space-y-3 p-4 [&_.admin-input]:!py-2 [&_.admin-label]:!mb-1 [&_.admin-select]:!py-2">
            <h3 class="text-sm font-semibold text-gray-900">Basic Information</h3>
            <div class="grid grid-cols-1 gap-x-5 gap-y-3 sm:grid-cols-2">
                <x-admin.form.input label="Product Name" name="name" :value="$product->name ?? null" required />
                <x-admin.form.input label="Slug" name="slug" :value="$product->slug ?? null" help="Leave blank to auto-generate. Must be unique." />
                <div>
                    <label for="parent_category_id" class="admin-label">Category <span class="text-error">*</span></label>
                    <select name="parent_category_id" id="parent_category_id" x-model="selectedParentCategory" x-on:change="selectedCategory = ''" required class="admin-select">
                        <option value="">Select category</option>
                        @foreach ($parentCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('parent_category_id')
                        <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="category_id" class="admin-label">Subcategory <span class="text-error">*</span></label>
                    <select name="category_id" id="category_id" x-model="selectedCategory" required class="admin-select" :disabled="!selectedParentCategory">
                        <option value="" x-text="selectedParentCategory ? 'Select subcategory' : 'Select category first'"></option>
                        <template x-for="category in filteredSubcategories()" :key="category.id">
                            <option :value="category.id" x-text="category.name"></option>
                        </template>
                    </select>
                    <p x-show="selectedParentCategory && filteredSubcategories().length === 0" class="mt-1 text-xs text-error">No subcategories found for this category.</p>
                    @error('category_id')
                        <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:col-span-2">
                    <x-admin.form.input label="SKU" name="sku" :value="$product->sku ?? null" required />
                    <x-admin.form.input label="Barcode" name="barcode" :value="$product->barcode ?? null" />
                    <x-admin.form.input label="Brand" name="brand" :value="$product->brand ?? 'Aurelle'" />
                </div>
            </div>
            <x-admin.form.textarea label="Short Description" name="short_description" :value="$product->short_description ?? null" :rows="2" />
            <x-admin.form.textarea label="Full Description" name="description" :value="$product->description ?? null" :rows="8" class="js-product-description-editor" />
        </div>

        {{-- Jewellery Information --}}
        <div class="admin-card space-y-3 p-4 [&_.admin-input]:!py-2 [&_.admin-label]:!mb-1 [&_.admin-select]:!py-2">
            <h3 class="text-sm font-semibold text-gray-900">Jewellery Information</h3>
            <div class="product-jewellery-grid">
                <div class="product-jewellery-type">
                    <label for="jewellery_type" class="admin-label">Jewellery Type <span class="text-error">*</span></label>
                    <select name="jewellery_type" id="jewellery_type" x-model="selectedJewelleryType" required class="admin-select">
                        <option value="">Select jewellery type</option>
                        <template x-for="type in allJewelleryTypes" :key="type.name">
                            <option :value="type.name" x-text="type.name"></option>
                        </template>
                    </select>
                    <p x-show="allJewelleryTypes.length === 0" class="mt-1 text-xs text-error">No active jewellery types found.</p>
                    @error('jewellery_type')
                        <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
                <div
                    x-data="{ collectionOpen: false, collectionSearch: '' }"
                    class="product-jewellery-collection relative"
                    @click.outside="collectionOpen = false"
                    @keydown.escape="collectionOpen = false"
                >
                    <label for="collection-toggle" class="admin-label">Collection <span class="text-error">*</span></label>

                    <button
                        type="button"
                        id="collection-toggle"
                        @click="collectionOpen = !collectionOpen; if (collectionOpen) $nextTick(() => $refs.collectionSearchInput.focus())"
                        class="admin-select flex min-h-[42px] w-full flex-wrap items-center gap-1.5 text-left"
                        :class="collectionOpen ? 'border-champagne-dark ring-2 ring-champagne/25' : ''"
                    >
                        <span x-show="selectedCollections.length === 0" class="text-gray-400">Select collections</span>
                        <template x-for="slug in selectedCollections" :key="slug">
                            <span class="inline-flex items-center gap-1 rounded-md bg-ivory-soft px-2 py-0.5 text-xs text-gray-700">
                                <span x-text="allCollections.find((c) => c.slug === slug)?.name ?? slug"></span>
                                <button type="button" @click.stop="selectedCollections = selectedCollections.filter((s) => s !== slug)" class="text-gray-400 hover:text-error">&times;</button>
                            </span>
                        </template>
                        <svg class="ml-auto h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="collectionOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </button>

                    <template x-for="slug in selectedCollections" :key="'collection-input-' + slug">
                        <input type="hidden" name="collection[]" :value="slug">
                    </template>

                    <div x-show="collectionOpen" x-cloak x-transition.opacity.duration.150ms class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg">
                        <div class="flex items-center gap-2 border-b border-gray-100 p-2">
                            <input x-ref="collectionSearchInput" type="text" x-model="collectionSearch" placeholder="Search collections..." class="admin-input !py-1.5 text-sm">
                            <button type="button" x-show="selectedCollections.length > 0" @click="selectedCollections = []" class="shrink-0 text-xs text-gray-400 hover:text-error">Clear</button>
                        </div>
                        <ul class="max-h-48 overflow-y-auto p-1">
                            <template x-for="collection in allCollections.filter((c) => c.name.toLowerCase().includes(collectionSearch.toLowerCase()))" :key="collection.slug">
                                <li>
                                    <label class="flex cursor-pointer items-center gap-2 rounded-md px-2.5 py-1.5 text-sm text-gray-700 hover:bg-ivory-soft">
                                        <input
                                            type="checkbox"
                                            :checked="selectedCollections.includes(collection.slug)"
                                            @change="selectedCollections.includes(collection.slug) ? (selectedCollections = selectedCollections.filter((s) => s !== collection.slug)) : selectedCollections.push(collection.slug)"
                                            class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40"
                                        >
                                        <span x-text="collection.name"></span>
                                    </label>
                                </li>
                            </template>
                            <li x-show="allCollections.filter((c) => c.name.toLowerCase().includes(collectionSearch.toLowerCase())).length === 0" class="px-2.5 py-2 text-sm text-gray-400">No collections found.</li>
                        </ul>
                    </div>

                    <p x-show="allCollections.length === 0" class="mt-1 text-xs text-error">No active collections found.</p>
                    @error('collection')
                        <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                    @error('collection.*')
                        <p class="mt-1 text-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="product-jewellery-fields">
                    <x-admin.form.select
                        label="Material"
                        name="metal_type"
                        required
                        :value="$product->metal_type ?? null"
                        :options="$metalTypes->mapWithKeys(fn ($type) => [$type->name => $type->name.($type->is_active ? '' : ' (Inactive)')])->all()"
                        placeholder="Select material"
                    />
                    <x-admin.form.input label="Finish / Plating" name="finish_plating" :value="$product->finish_plating ?? null" placeholder="e.g. Gold plated, Oxidised" />
                    <x-admin.form.input label="Colour" name="metal_colour" :value="$product->metal_colour ?? null" />
                    <x-admin.form.input label="Stone Type" name="gemstone_type" :value="$product->gemstone_type ?? null" />
                    <x-admin.form.input label="Stone Colour" name="gemstone_colour" :value="$product->gemstone_colour ?? null" />
                    <x-admin.form.select label="Gender" name="gender" :value="$product->gender ?? null" :options="$genderOptions" placeholder="Select gender" />
                    <x-admin.form.input label="Gross Weight (g)" name="gross_weight" type="number" step="0.001" :value="$product->gross_weight ?? null" />
                    <div class="product-jewellery-toggles">
                        <label class="flex min-h-[42px] items-center gap-2.5 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_adjustable" value="1" @checked(old('is_adjustable', $product->is_adjustable ?? false)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                            <span>Adjustable</span>
                        </label>
                        <label class="flex min-h-[42px] items-center gap-2.5 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_water_resistant" value="1" @checked(old('is_water_resistant', $product->is_water_resistant ?? false)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                            <span>Water Resistant</span>
                        </label>
                        <label class="flex min-h-[42px] items-center gap-2.5 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_return_available" value="1" @checked(old('is_return_available', $product->is_return_available ?? false)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                            <span>Return Available</span>
                        </label>
                        <label class="flex min-h-[42px] items-center gap-2.5 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_refund_available" value="1" @checked(old('is_refund_available', $product->is_refund_available ?? false)) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                            <span>Refund Available</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="admin-card space-y-3 p-4 [&_.admin-input]:!py-2 [&_.admin-label]:!mb-1 [&_.admin-select]:!py-2">
            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
                <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                    <h3 class="text-sm font-semibold text-gray-900">Pricing</h3>
                    <p class="text-xs text-gray-500">Offer expiry uses {{ config('app.timezone') }}. Leave blank for no expiry.</p>
                </div>
                @if (isset($product))
                    <p class="rounded-lg bg-ivory-soft px-3 py-1.5 text-xs text-gray-600">Final Price: <span class="font-semibold text-gray-900">₹{{ number_format($product->final_price, 2) }}</span></p>
                @endif
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <x-admin.form.input label="MRP (₹)" name="mrp" type="number" step="0.01" required :value="$product->mrp ?? null" />
                <x-admin.form.input label="Selling Price (₹)" name="selling_price" type="number" step="0.01" required :value="$product->selling_price ?? null" />
                <x-admin.form.input label="Offer Price (₹)" name="offer_price" type="number" step="0.01" :value="$product->offer_price ?? null" />
                <div>
                    <x-admin.form.input label="Offer Expiry Date" name="offer_expiry_date" type="date" :value="isset($product) ? $product->offer_expiry_date?->format('Y-m-d') : null" />
                    @if (isset($product) && $product->offer_price !== null && $product->offerHasExpired())
                        <p class="mt-1 text-xs text-error">Offer expired; selling price applies.</p>
                    @endif
                </div>
                <x-admin.form.input label="GST Percentage (%)" name="gst_percentage" type="number" step="0.01" :value="$product->gst_percentage ?? 0" />
            </div>
        </div>

        {{-- Variants --}}
        <div class="admin-card space-y-5 p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Product Variants</h3>
                <button type="button" @click="addVariant()" class="admin-btn-secondary !px-3 !py-1.5 text-xs">+ Add Variant</button>
            </div>
            <p class="text-xs text-gray-400">Add size, metal, purity or colour variants with their own SKU, price and stock.</p>

            <template x-for="(variant, i) in variants" :key="i">
                <div class="grid grid-cols-2 gap-3 rounded-lg border border-gray-200 p-4 sm:grid-cols-7">
                    <input type="text" :name="`variants[${i}][size]`" x-model="variant.size" placeholder="Size" class="admin-input col-span-1">
                    <input type="text" :name="`variants[${i}][metal]`" x-model="variant.metal" placeholder="Metal" class="admin-input col-span-1">
                    <input type="text" :name="`variants[${i}][purity]`" x-model="variant.purity" placeholder="Purity" class="admin-input col-span-1">
                    <input type="text" :name="`variants[${i}][colour]`" x-model="variant.colour" placeholder="Colour" class="admin-input col-span-1">
                    <input type="number" step="0.01" :name="`variants[${i}][price]`" x-model="variant.price" placeholder="Price" class="admin-input col-span-1">
                    <input type="number" :name="`variants[${i}][stock_quantity]`" x-model="variant.stock_quantity" placeholder="Stock" class="admin-input col-span-1">
                    <button type="button" @click="removeVariant(i)" class="flex items-center justify-center rounded-lg border border-gray-200 text-error hover:bg-error/5">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
                    </button>
                </div>
            </template>

            <p x-show="variants.length === 0" class="text-sm text-gray-400">No variants added. This product will be sold as a single SKU.</p>
        </div>

        {{-- SEO --}}
        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">SEO</h3>
            <x-admin.form.input label="Meta Title" name="meta_title" :value="$product->meta_title ?? null" />
            <x-admin.form.textarea label="Meta Description" name="meta_description" :value="$product->meta_description ?? null" :rows="3" />
            <x-admin.form.input label="Meta Keywords" name="meta_keywords" :value="$product->meta_keywords ?? null" help="Comma-separated keywords." />
        </div>
    </div>

    <div class="space-y-6">
        {{-- Images --}}
        <div class="admin-card space-y-4 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Product Images</h3>

            @if (isset($product) && $product->images->count() > 0)
                <div class="grid grid-cols-3 gap-2">
                    @foreach ($product->images as $image)
                        <div class="group relative">
                            <x-ui.optimized-image :src="asset('storage/'.$image->image_path)" alt="" sizes="120px" class="aspect-square w-full rounded-lg border {{ $image->is_primary ? 'border-champagne-dark ring-1 ring-champagne-dark' : 'border-gray-200' }} object-cover" />
                            @if ($image->is_primary)
                                <span class="absolute left-1 top-1 rounded bg-champagne-dark px-1.5 py-0.5 text-[9px] font-semibold uppercase text-white">Primary</span>
                            @endif
                            <div class="absolute inset-0 flex items-center justify-center gap-1.5 rounded-lg bg-black/50 opacity-0 transition-opacity group-hover:opacity-100">
                                @if (! $image->is_primary)
                                    <button type="button" onclick="submitProductImageAction('{{ route('admin.products.images.primary', [$product, $image]) }}', 'PATCH')" class="rounded-full bg-white p-1.5 text-gray-700" title="Set as primary">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.6 5.6 6.1.6-4.6 4.2 1.3 6.1L12 15l-5.4 3 1.3-6.1L3.3 8.2l6.1-.6L12 2z" /></svg>
                                    </button>
                                @endif
                                <button type="button" onclick="submitProductImageAction('{{ route('admin.products.images.destroy', [$product, $image]) }}', 'DELETE', 'Remove this image?')" class="rounded-full bg-white p-1.5 text-error" title="Remove">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div>
                <label class="admin-label">Upload New Images</label>
                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp,image/avif" class="admin-input">
                <p class="mt-1 text-xs text-gray-400">JPG, PNG, WebP or AVIF only. Max 5MB each. Uploads are optimized to responsive AVIF/WebP files.</p>
                @error('images')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
                @error('images.*')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Diamond Details --}}
        <div class="admin-card space-y-5 p-6">
            <label class="flex items-center gap-2.5 text-sm font-semibold text-gray-900">
                <input type="checkbox" name="has_diamond" value="1" x-model="hasDiamond" class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                Has Diamond
            </label>
            <div x-show="hasDiamond" x-cloak x-collapse class="grid grid-cols-3 gap-4">
                <x-admin.form.input label="Carat" name="diamond_carat" type="number" step="0.001" :value="$product->diamond_carat ?? null" />
                <x-admin.form.input label="Diamond Count" name="diamond_count" type="number" :value="$product->diamond_count ?? null" />
                <x-admin.form.input label="Colour" name="diamond_colour" :value="$product->diamond_colour ?? null" placeholder="e.g. VS-EF" />
                <x-admin.form.input label="Clarity" name="diamond_clarity" :value="$product->diamond_clarity ?? null" placeholder="e.g. VVS1" />
                <x-admin.form.input label="Cut" name="diamond_cut" :value="$product->diamond_cut ?? null" placeholder="e.g. Excellent" />
                <x-admin.form.input label="Shape" name="diamond_shape" :value="$product->diamond_shape ?? null" placeholder="e.g. Round Brilliant" />
            </div>
        </div>

        {{-- Gemstone Details --}}
        <div class="admin-card space-y-5 p-6">
            <label class="flex items-center gap-2.5 text-sm font-semibold text-gray-900">
                <input type="checkbox" name="has_gemstone" value="1" x-model="hasGemstone" class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                Has Gemstone
            </label>
            <div x-show="hasGemstone" x-cloak x-collapse class="grid grid-cols-1 gap-4">
                <x-admin.form.input label="Weight (ct)" name="gemstone_weight" type="number" step="0.001" :value="$product->gemstone_weight ?? null" />
            </div>
        </div>

        {{-- Inventory --}}
        <div class="admin-card space-y-5 p-6">
            <h3 class="text-sm font-semibold text-gray-900">Inventory</h3>
            <x-admin.form.input label="Stock Quantity" name="stock_quantity" type="number" required :value="$product->stock_quantity ?? 0" />
            <x-admin.form.input label="Minimum Stock" name="minimum_stock" type="number" :value="$product->minimum_stock ?? 5" />
            @if (isset($product))
                <div><span class="admin-label !mb-1">Current Stock Status</span><x-admin.status-badge :status="$product->stock_status" /></div>
            @endif
        </div>

        {{-- Flags --}}
        <div class="admin-card space-y-3 p-6">
            <h3 class="mb-1 text-sm font-semibold text-gray-900">Flags</h3>
            @foreach ([
                ['name' => 'is_active', 'label' => 'Active', 'default' => true],
                ['name' => 'is_featured', 'label' => 'Featured'],
                ['name' => 'is_new_arrival', 'label' => 'New Arrival'],
                ['name' => 'is_best_seller', 'label' => 'Best Seller'],
                ['name' => 'is_trending', 'label' => 'Trending'],
                ['name' => 'is_on_sale', 'label' => 'On Sale'],
            ] as $flag)
                <label class="flex items-center gap-2.5 text-sm text-gray-700">
                    <input type="checkbox" name="{{ $flag['name'] }}" value="1" @checked(old($flag['name'], $product->{$flag['name']} ?? ($flag['default'] ?? false))) class="h-4 w-4 rounded border-gray-300 text-champagne-dark focus:ring-champagne-dark/40">
                    {{ $flag['label'] }}
                </label>
            @endforeach
        </div>

        <button type="submit" class="admin-btn-primary w-full disabled:cursor-wait disabled:opacity-70" x-bind:disabled="submitting">
            <svg x-show="submitting" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"></circle>
                <path class="opacity-90" d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.8s" repeatCount="indefinite" />
            </svg>
            <span x-text="submitting ? '{{ isset($product) ? 'Updating Product...' : 'Creating Product...' }}' : '{{ isset($product) ? 'Update Product' : 'Create Product' }}'">{{ isset($product) ? 'Update Product' : 'Create Product' }}</span>
        </button>
    </div>
</div>

@once
    @push('scripts')
        <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
        <script>
            window.submitProductImageAction = function (action, method, confirmation) {
                if (confirmation && !window.confirm(confirmation)) {
                    return;
                }

                var form = document.createElement('form');
                form.method = 'POST';
                form.action = action;
                form.className = 'hidden';

                var token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = '{{ csrf_token() }}';
                form.appendChild(token);

                if (method && method.toUpperCase() !== 'POST') {
                    var methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = method.toUpperCase();
                    form.appendChild(methodInput);
                }

                document.body.appendChild(form);
                form.submit();
            };

            function initProductDescriptionEditor() {
                var textarea = document.querySelector('.js-product-description-editor');

                if (!textarea) {
                    return;
                }

                if (!window.CKEDITOR) {
                    var warning = document.createElement('p');
                    warning.className = 'mt-1 text-xs text-error';
                    warning.textContent = 'Rich text editor could not load. You can still enter the description as plain text.';
                    textarea.insertAdjacentElement('afterend', warning);
                    return;
                }

                if (CKEDITOR.instances[textarea.id]) {
                    return;
                }

                var editor = CKEDITOR.replace(textarea.id, {
                    height: 260,
                    versionCheck: false,
                    removeButtons: 'About,Anchor,Styles,Flash,Iframe,Save,NewPage,Preview,Print',
                    contentsCss: [
                        'https://fonts.bunny.net/css?family=inter:400,500,600',
                    ],
                    bodyClass: 'product-description-editor',
                    toolbar: [
                        { name: 'clipboard', items: ['Undo', 'Redo'] },
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'RemoveFormat'] },
                        { name: 'paragraph', items: ['NumberedList', 'BulletedList', 'Blockquote', 'JustifyLeft', 'JustifyCenter'] },
                        { name: 'links', items: ['Link', 'Unlink'] },
                        { name: 'insert', items: ['Table', 'HorizontalRule'] },
                        { name: 'tools', items: ['Maximize'] }
                    ]
                });

                editor.on('change', function () {
                    editor.updateElement();
                });

                textarea.closest('form')?.addEventListener('submit', function () {
                    editor.updateElement();
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initProductDescriptionEditor);
            } else {
                initProductDescriptionEditor();
            }
        </script>
    @endpush
@endonce

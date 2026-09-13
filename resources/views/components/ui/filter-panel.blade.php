@props([
    'model' => 'filters',
])

@php
    $sections = [
        'category' => ['label' => 'Category', 'type' => 'checkbox', 'options' => ['rings' => 'Rings', 'earrings' => 'Earrings', 'necklaces' => 'Necklaces', 'pendants' => 'Pendants', 'bracelets' => 'Bracelets', 'bangles' => 'Bangles', 'chains' => 'Chains']],
        'metal' => ['label' => 'Metal Type', 'type' => 'checkbox', 'options' => ['Gold' => 'Gold', 'Rose Gold' => 'Rose Gold', 'White Gold' => 'White Gold', 'Silver' => 'Silver', 'Platinum' => 'Platinum']],
        'purity' => ['label' => 'Gold Purity', 'type' => 'checkbox', 'options' => ['14K' => '14K', '18K' => '18K', '22K' => '22K', '24K' => '24K']],
        'gender' => ['label' => 'Gender', 'type' => 'checkbox', 'options' => ['Women' => 'Women', 'Men' => 'Men', 'Unisex' => 'Unisex']],
        'occasion' => ['label' => 'Occasion', 'type' => 'checkbox', 'options' => ['wedding' => 'Wedding', 'engagement' => 'Engagement', 'anniversary' => 'Anniversary', 'festive' => 'Festive', 'everyday' => 'Everyday', 'birthday' => 'Birthday']],
        'rating' => ['label' => 'Rating', 'type' => 'checkbox', 'options' => ['4' => '4★ & above', '3' => '3★ & above']],
        'availability' => ['label' => 'Availability', 'type' => 'checkbox', 'options' => ['in_stock' => 'In Stock Only']],
        'flags' => ['label' => 'Highlights', 'type' => 'checkbox', 'options' => ['new' => 'New Arrivals', 'bestseller' => 'Best Sellers', 'discount' => 'On Discount']],
    ];
@endphp

<div class="divide-y divide-line">
    <div class="flex items-center justify-between pb-4">
        <span class="text-sm font-semibold text-charcoal">Filters</span>
        <button type="button" @click="{{ $model }} = { category: [], metal: [], purity: [], gender: [], occasion: [], rating: [], availability: [], flags: [], priceMin: 0, priceMax: 500000 }" class="text-xs font-medium text-champagne-dark hover:underline">
            Clear All
        </button>
    </div>

    <div class="py-4" x-data="{ open: true }">
        <button type="button" @click="open = !open" class="flex w-full items-center justify-between">
            <span class="text-sm font-medium text-charcoal">Price Range</span>
            <svg class="h-3.5 w-3.5 text-muted transition-transform" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
        </button>
        <div x-cloak x-show="open" x-collapse class="mt-4 space-y-3">
            <input type="range" x-model.number="{{ $model }}.priceMax" min="1000" max="500000" step="1000" class="w-full accent-champagne-dark">
            <div class="flex items-center gap-2">
                <input type="number" x-model.number="{{ $model }}.priceMin" placeholder="Min" class="input-luxe !py-2 text-xs">
                <span class="text-muted-light">&ndash;</span>
                <input type="number" x-model.number="{{ $model }}.priceMax" placeholder="Max" class="input-luxe !py-2 text-xs">
            </div>
        </div>
    </div>

    @foreach ($sections as $key => $section)
        <div class="py-4" x-data="{ open: {{ in_array($key, ['category', 'metal']) ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between">
                <span class="text-sm font-medium text-charcoal">{{ $section['label'] }}</span>
                <svg class="h-3.5 w-3.5 text-muted transition-transform" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </button>
            <div x-cloak x-show="open" x-collapse class="mt-3 space-y-2.5">
                @foreach ($section['options'] as $value => $label)
                    <label class="flex cursor-pointer items-center gap-2.5 text-[13.5px] text-charcoal-soft">
                        <input type="checkbox" value="{{ $value }}" x-model="{{ $model }}.{{ $key }}" class="h-4 w-4 rounded border-line text-champagne-dark focus:ring-champagne-dark/40">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<?php

namespace Database\Seeders;

use App\Models\JewelleryCollection;
use App\Models\JewelleryType;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    protected const STYLE_TYPES = [
        'Gold-plated',
        'Silver-plated',
        'Rose-gold-plated',
        'Oxidised',
        'American Diamond',
        'Kundan',
        'Pearl',
        'Artificial Stone',
        'Handmade',
        'Minimal',
        'Traditional',
        'Western',
    ];

    protected const USAGE_COLLECTIONS = [
        ['slug' => 'daily-wear', 'name' => 'Daily Wear', 'description' => 'Easy jewellery styles for everyday use.'],
        ['slug' => 'office-wear', 'name' => 'Office Wear', 'description' => 'Polished jewellery suitable for workwear.'],
        ['slug' => 'casual-wear', 'name' => 'Casual Wear', 'description' => 'Relaxed jewellery for casual styling.'],
        ['slug' => 'college-wear', 'name' => 'College Wear', 'description' => 'Lightweight styles for campus and young everyday looks.'],
        ['slug' => 'party-wear', 'name' => 'Party Wear', 'description' => 'Statement jewellery for evenings and parties.'],
        ['slug' => 'festive-wear', 'name' => 'Festive Wear', 'description' => 'Celebration-ready jewellery for festive occasions.'],
        ['slug' => 'wedding-wear', 'name' => 'Wedding Wear', 'description' => 'Dressy jewellery for wedding functions and ceremonies.'],
        ['slug' => 'gift-jewellery', 'name' => 'Gift Jewellery', 'description' => 'Giftable jewellery picks for special moments.'],
    ];

    public function run(): void
    {
        foreach (self::STYLE_TYPES as $i => $type) {
            JewelleryType::query()->updateOrCreate(
                ['slug' => Str::slug($type)],
                ['name' => $type, 'sort_order' => $i, 'is_active' => true]
            );
        }

        foreach (self::USAGE_COLLECTIONS as $i => $collection) {
            JewelleryCollection::query()->updateOrCreate(
                ['slug' => $collection['slug']],
                [
                    'name' => $collection['name'],
                    'description' => $collection['description'],
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );
        }

        Product::query()
            ->get()
            ->each(function (Product $product) {
                $data = [];

                if (! JewelleryType::query()->where('name', $product->jewellery_type)->exists()) {
                    $data['jewellery_type'] = $this->typeForProduct($product);
                }

                $collectionSlugs = $product->collectionSlugs();
                $hasMissingCollection = $collectionSlugs === [] || collect($collectionSlugs)
                    ->contains(fn ($slug) => ! JewelleryCollection::query()->where('slug', $slug)->exists());

                if ($hasMissingCollection) {
                    $data['collection'] = json_encode([$this->collectionForProduct($product)]);
                }

                if ($data !== []) {
                    $product->updateQuietly($data);
                }
            });
    }

    protected function collectionForProduct(Product $product): string
    {
        $text = Str::lower(($product->category?->name ?? '').' '.$product->name.' '.$product->jewellery_type);

        if (str_contains($text, 'bridal') || str_contains($text, 'wedding')) {
            return 'wedding-wear';
        }

        if (str_contains($text, 'festive') || str_contains($text, 'kundan') || str_contains($text, 'bangle')) {
            return 'festive-wear';
        }

        if ((float) $product->final_price >= 50000) {
            return 'party-wear';
        }

        return 'daily-wear';
    }

    protected function typeForProduct(Product $product): string
    {
        $text = Str::lower($product->name.' '.$product->metal_type.' '.$product->jewellery_type);

        if ($product->has_diamond || str_contains($text, 'diamond')) {
            return 'American Diamond';
        }

        if (str_contains($text, 'kundan')) {
            return 'Kundan';
        }

        if (str_contains($text, 'pearl')) {
            return 'Pearl';
        }

        if (str_contains($text, 'rose')) {
            return 'Rose-gold-plated';
        }

        if (str_contains($text, 'silver')) {
            return 'Silver-plated';
        }

        if (str_contains($text, 'gold')) {
            return 'Gold-plated';
        }

        return 'Minimal';
    }
}

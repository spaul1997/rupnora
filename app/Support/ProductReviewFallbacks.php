<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductReviewFallbacks
{
    private const FILE = 'product-reviews.json';

    /**
     * Return the saved fallback reviews for a product, generating them once when needed.
     */
    public function forProduct(array $product, int $count = 6): array
    {
        $key = (string) ($product['slug'] ?? $product['id']);
        $store = $this->readStore();
        $savedReviews = $store['products'][$key]['reviews'] ?? null;

        if (is_array($savedReviews) && count($savedReviews) >= $count) {
            return array_slice($savedReviews, 0, $count);
        }

        $reviews = $this->generate($product, $count);

        $store['products'][$key] = [
            'product_id' => $product['id'] ?? null,
            'product_name' => $product['name'] ?? 'Jewellery',
            'jewellery_type' => $product['type'] ?? $product['category_name'] ?? 'Jewellery',
            'generated_at' => now()->toIso8601String(),
            'reviews' => $reviews,
        ];

        Storage::disk('local')->put(
            self::FILE,
            json_encode($store, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
        );

        return $reviews;
    }

    private function readStore(): array
    {
        if (! Storage::disk('local')->exists(self::FILE)) {
            return ['version' => 1, 'products' => []];
        }

        $store = json_decode(Storage::disk('local')->get(self::FILE), true);

        if (! is_array($store) || ! isset($store['products']) || ! is_array($store['products'])) {
            return ['version' => 1, 'products' => []];
        }

        return $store;
    }

    private function generate(array $product, int $count): array
    {
        $name = (string) ($product['name'] ?? 'this piece');
        $metal = trim((string) ($product['metal'] ?? ''));
        $type = trim((string) ($product['type'] ?? $product['category_name'] ?? 'jewellery'));
        $descriptor = Str::lower($type ?: 'jewellery');
        $seed = (string) ($product['slug'] ?? $product['id'] ?? $name);
        $templates = $this->templatesFor($descriptor);
        $names = $this->rotate([
            'Aarohi Mehta', 'Nisha Kapoor', 'Meera Iyer', 'Diya Shah', 'Ananya Rao', 'Riya Malhotra',
            'Ishita Nair', 'Kavya Menon', 'Neha Joshi', 'Sanya Gupta', 'Pooja Reddy', 'Tanya Singh',
        ], $this->number($seed, 'names'));
        $locations = $this->rotate([
            'Mumbai', 'Bengaluru', 'Delhi', 'Hyderabad', 'Chennai', 'Pune',
            'Kochi', 'Jaipur', 'Ahmedabad', 'Kolkata', 'Surat', 'Lucknow',
        ], $this->number($seed, 'locations'));
        $templates = $this->rotate($templates, $this->number($seed, 'templates'));
        $ratings = $this->rotate([5, 5, 4, 5, 4, 5], $this->number($seed, 'ratings'));
        $material = $metal !== '' ? Str::lower($metal) : 'jewellery';
        $reviews = [];

        for ($index = 0; $index < $count; $index++) {
            $text = strtr($templates[$index % count($templates)], [
                ':name' => $name,
                ':type' => $descriptor,
                ':metal' => $material,
            ]);

            $reviews[] = [
                'name' => $names[$index % count($names)],
                'location' => $locations[$index % count($locations)],
                'rating' => $ratings[$index % count($ratings)],
                'verified' => false,
                'date' => now()->subDays(18 + ($index * 24) + ($this->number($seed, 'date-'.$index) % 12))->format('M Y'),
                'text' => $text,
            ];
        }

        return $reviews;
    }

    private function templatesFor(string $type): array
    {
        $specific = match (true) {
            str_contains($type, 'ring') => [
                'The fit of :name is very comfortable, and the setting looks even more refined in person.',
                'I chose this :type for a special occasion. It sits beautifully on the finger without feeling heavy.',
                'The band is smooth, the sizing was accurate, and the small details catch the light beautifully.',
            ],
            str_contains($type, 'earring') => [
                ':name feels surprisingly light and comfortable even after wearing it for the whole evening.',
                'The clasps feel secure, and this :type frames the face beautifully without looking oversized.',
                'I love how the pair catches the light. The finish is neat and both pieces match perfectly.',
            ],
            str_contains($type, 'necklace') || str_contains($type, 'set') => [
                ':name sits beautifully along the neckline and looks much more detailed in person.',
                'The proportions of this :type are elegant, and it paired perfectly with my festive outfit.',
                'It makes a statement without being uncomfortable. The finishing on the reverse is neat too.',
            ],
            str_contains($type, 'pendant') => [
                ':name is delicate enough for everyday wear while still catching the light beautifully.',
                'The pendant sits neatly at the neckline and looks lovely both on its own and layered.',
                'The scale is just right, and the fine detailing is much clearer when seen in person.',
            ],
            str_contains($type, 'bracelet') => [
                ':name rests comfortably on the wrist, and the clasp feels secure for regular wear.',
                'The links move smoothly and this :type has an elegant shine without feeling too flashy.',
                'I was impressed by the balanced weight and the clean finish around the clasp.',
            ],
            str_contains($type, 'bangle') || str_contains($type, 'kada') => [
                ':name has a comfortable rounded edge and the size matched the guide perfectly.',
                'This :type stacks beautifully with my other bangles but also looks elegant worn alone.',
                'The engraving is crisp, and the weight feels reassuring without being uncomfortable.',
            ],
            str_contains($type, 'chain') => [
                'The links on :name are beautifully finished and lie flat without twisting during the day.',
                'This :type has a lovely weight, a secure clasp, and just the right amount of shine.',
                'It looks polished worn alone and is also substantial enough to hold my favourite pendant.',
            ],
            default => [
                ':name has graceful proportions and looks even better in natural light.',
                'The details on this :type are clean and precise, and it feels comfortable to wear.',
                'A beautifully finished piece that works just as well for celebrations as for everyday styling.',
            ],
        };

        return array_merge($specific, [
            'The :metal finish looks rich and even. The craftsmanship on :name is excellent for the price.',
            ':name arrived safely packed and matched the product photos very closely.',
            'The design feels timeless, the finishing is smooth, and this :type has quickly become a favourite.',
        ]);
    }

    private function rotate(array $values, int $seed): array
    {
        $offset = $seed % count($values);

        return array_merge(array_slice($values, $offset), array_slice($values, 0, $offset));
    }

    private function number(string $seed, string $salt): int
    {
        return (int) sprintf('%u', crc32($seed.'|'.$salt));
    }
}

<?php

namespace App\Support;

use App\Models\WebsiteSetting;
use Illuminate\Support\Str;

class Seo
{
    public const DEFAULT_DESCRIPTION = 'Shop Rupnora jewellery online, including gold, diamond and silver rings, earrings, necklaces, bracelets and occasion-ready collections.';

    public static function pageTitle(?string $title): string
    {
        $title = Str::squish(strip_tags((string) $title));

        if ($title === '') {
            return 'Rupnora | Gold, Diamond & Silver Jewellery Online';
        }

        return Str::contains(Str::lower($title), 'rupnora') ? $title : $title.' — Rupnora';
    }

    public static function description(?string $description, ?string $routeName = null): string
    {
        $description = Str::squish(strip_tags((string) $description));

        if ($description === '') {
            $description = self::routeDescriptions()[$routeName] ?? self::DEFAULT_DESCRIPTION;
        }

        return Str::limit($description, 160, '…');
    }

    public static function isIndexableRoute(?string $routeName): bool
    {
        if (! $routeName) {
            return false;
        }

        return Str::is([
            'home',
            'about',
            'careers',
            'size-guide',
            'influencer',
            'privacy-policy',
            'terms-of-service',
            'refund-policy',
            'contact',
            'collections.index',
            'collection.show',
            'categories.index',
            'new-arrivals',
            'best-sellers',
            'jewellery-type.show',
            'category.show',
            'recipient.show',
            'product.show',
        ], $routeName);
    }

    public static function absoluteUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return url('/'.ltrim($url, '/'));
    }

    /**
     * @param  array<int, array{name: string, url?: string|null}>  $breadcrumbs
     * @param  array<int, array<string, mixed>>  $extraNodes
     * @return array<string, mixed>
     */
    public static function graph(
        string $title,
        string $description,
        string $canonical,
        ?string $image,
        string $pageType,
        array $breadcrumbs,
        array $extraNodes,
        WebsiteSetting $settings,
    ): array {
        $homeUrl = route('home');
        $organizationId = $homeUrl.'#organization';
        $websiteId = $homeUrl.'#website';
        $pageId = $canonical.'#webpage';
        $sameAs = collect([$settings->facebook, $settings->instagram, $settings->linkedin, $settings->youtube])
            ->filter()
            ->values()
            ->all();

        $organization = array_filter([
            '@type' => ['Organization', 'OnlineStore'],
            '@id' => $organizationId,
            'name' => $settings->company_name ?: 'Rupnora',
            'url' => $homeUrl,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('logo.png'),
            ],
            'image' => asset('logo-tag.png'),
            'email' => $settings->support_email,
            'telephone' => $settings->phone,
            'address' => $settings->address ? [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings->address,
                'addressCountry' => 'IN',
            ] : null,
            'sameAs' => $sameAs ?: null,
        ], fn ($value) => $value !== null && $value !== '');

        $webPage = array_filter([
            '@type' => $pageType,
            '@id' => $pageId,
            'url' => $canonical,
            'name' => $title,
            'description' => $description,
            'inLanguage' => 'en-IN',
            'isPartOf' => ['@id' => $websiteId],
            'about' => ['@id' => $organizationId],
            'primaryImageOfPage' => $image ? [
                '@type' => 'ImageObject',
                'url' => $image,
            ] : null,
        ], fn ($value) => $value !== null);

        $mainEntity = collect($extraNodes)->first(fn (array $node) => isset($node['@id']));

        if ($mainEntity) {
            $webPage['mainEntity'] = ['@id' => $mainEntity['@id']];
        }

        $nodes = [
            $organization,
            [
                '@type' => 'WebSite',
                '@id' => $websiteId,
                'url' => $homeUrl,
                'name' => 'Rupnora',
                'publisher' => ['@id' => $organizationId],
                'inLanguage' => 'en-IN',
            ],
            $webPage,
        ];

        if ($breadcrumbs !== []) {
            $nodes[] = [
                '@type' => 'BreadcrumbList',
                '@id' => $canonical.'#breadcrumb',
                'itemListElement' => collect($breadcrumbs)
                    ->values()
                    ->map(fn (array $crumb, int $index) => array_filter([
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $crumb['name'],
                        'item' => $crumb['url'] ?? null,
                    ], fn ($value) => $value !== null))
                    ->all(),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => array_values(array_merge($nodes, $extraNodes)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function productSchema(array $product, string $canonical): array
    {
        $description = Str::squish(strip_tags((string) ($product['meta_description'] ?? $product['short_desc'] ?? $product['description'] ?? '')));
        $images = collect($product['gallery'] ?? [])
            ->prepend($product['primary_image'] ?? $product['image'] ?? null)
            ->filter()
            ->map(fn (string $image) => self::absoluteUrl($image))
            ->unique()
            ->values()
            ->all();

        $schema = array_filter([
            '@type' => 'Product',
            '@id' => $canonical.'#product',
            'name' => $product['name'],
            'url' => $canonical,
            'image' => $images ?: null,
            'description' => $description !== '' ? self::description($description) : null,
            'sku' => $product['sku'] ?? null,
            'brand' => filled($product['brand'] ?? null) ? [
                '@type' => 'Brand',
                'name' => $product['brand'],
            ] : null,
            'category' => $product['category_name'] ?? null,
            'material' => $product['metal'] ?? null,
            'color' => $product['colour'] ?? null,
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonical,
                'priceCurrency' => 'INR',
                'price' => number_format((float) $product['price'], 2, '.', ''),
                'availability' => ($product['in_stock'] ?? false)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
            ],
        ], fn ($value) => $value !== null);

        if (($product['rating'] ?? 0) > 0 && ($product['ratings_count'] ?? 0) > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (float) $product['rating'],
                'bestRating' => 5,
                'worstRating' => 1,
                'ratingCount' => (int) $product['ratings_count'],
                'reviewCount' => (int) ($product['reviews_count'] ?? 0),
            ];
        }

        return $schema;
    }

    /**
     * @return array<string, string>
     */
    protected static function routeDescriptions(): array
    {
        return [
            'home' => self::DEFAULT_DESCRIPTION,
            'about' => 'Learn about Rupnora and our approach to thoughtfully curated jewellery, clear product information and customer care.',
            'careers' => 'Explore careers at Rupnora across jewellery, content, ecommerce, operations and customer experience.',
            'size-guide' => 'Use the Rupnora jewellery size guide to measure rings, bracelets and necklaces and find a comfortable fit.',
            'influencer' => 'Join the Rupnora influencer program and collaborate with us to create meaningful jewellery stories.',
            'privacy-policy' => 'Read the Rupnora privacy policy and learn how we collect, use and protect your information.',
            'terms-of-service' => 'Read the terms that apply when browsing and shopping on the Rupnora website.',
            'refund-policy' => 'Review Rupnora return, refund and eligibility information before or after placing an order.',
            'contact' => 'Contact Rupnora for jewellery guidance, order support, shipping, returns and general enquiries.',
            'collections.index' => 'Explore curated Rupnora jewellery collections for everyday wear, gifting, celebrations, weddings and special occasions.',
            'categories.index' => 'Browse Rupnora jewellery by category, including rings, earrings, necklaces, bracelets, pendants and more.',
            'new-arrivals' => 'Discover the latest jewellery arrivals at Rupnora, with newly added designs for everyday wear and special occasions.',
            'best-sellers' => 'Shop Rupnora best-selling jewellery and discover customer favourites across our most-loved categories.',
        ];
    }
}

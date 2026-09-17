<?php

namespace App\Support;

class Catalog
{
    public static function categories(): array
    {
        return [
            ['slug' => 'rings', 'name' => 'Rings', 'art' => 'ring', 'blurb' => 'Engagement, wedding & everyday rings', 'count' => 86],
            ['slug' => 'earrings', 'name' => 'Earrings', 'art' => 'earring', 'blurb' => 'Studs, hoops & drops', 'count' => 64],
            ['slug' => 'necklaces', 'name' => 'Necklaces', 'art' => 'necklace', 'blurb' => 'Statement & layering pieces', 'count' => 52],
            ['slug' => 'pendants', 'name' => 'Pendants', 'art' => 'pendant', 'blurb' => 'Delicate everyday charms', 'count' => 41],
            ['slug' => 'bracelets', 'name' => 'Bracelets', 'art' => 'bracelet', 'blurb' => 'Tennis, cuff & chain styles', 'count' => 37],
            ['slug' => 'bangles', 'name' => 'Bangles', 'art' => 'bangle', 'blurb' => 'Classic & kada designs', 'count' => 45],
            ['slug' => 'chains', 'name' => 'Chains', 'art' => 'chain', 'blurb' => 'Gold & silver chains', 'count' => 29],
            ['slug' => 'mens', 'name' => "Men's Jewellery", 'art' => 'mens', 'blurb' => 'Chains, rings & bracelets', 'count' => 33],
            ['slug' => 'gold', 'name' => 'Gold Jewellery', 'art' => 'gold', 'blurb' => '14K to 24K craftsmanship', 'count' => 148],
            ['slug' => 'diamond', 'name' => 'Diamond Jewellery', 'art' => 'diamond', 'blurb' => 'IGI certified brilliance', 'count' => 92],
            ['slug' => 'silver', 'name' => 'Silver Jewellery', 'art' => 'silver', 'blurb' => '925 sterling silver', 'count' => 58],
            ['slug' => 'bridal', 'name' => 'Bridal Jewellery', 'art' => 'bridal', 'blurb' => 'Complete bridal sets', 'count' => 24],
        ];
    }

    public static function category(string $slug): ?array
    {
        return collect(self::categories())->firstWhere('slug', $slug);
    }

    public static function collections(): array
    {
        return [
            ['slug' => 'diamond-collection', 'name' => 'The Diamond Collection', 'art' => 'diamond', 'blurb' => 'Brilliance for every occasion', 'tag' => '38 Designs'],
            ['slug' => 'everyday-gold', 'name' => 'Everyday Gold', 'art' => 'chain', 'blurb' => 'Lightweight pieces for daily wear', 'tag' => '52 Designs'],
            ['slug' => 'wedding-collection', 'name' => 'Wedding Collection', 'art' => 'bridal', 'blurb' => 'Heirlooms in the making', 'tag' => '24 Designs'],
            ['slug' => 'minimal-collection', 'name' => 'Minimal Collection', 'art' => 'pendant', 'blurb' => 'Understated, modern elegance', 'tag' => '30 Designs'],
            ['slug' => 'festive-collection', 'name' => 'Festive Collection', 'art' => 'bangle', 'blurb' => 'Statement pieces for celebration', 'tag' => '27 Designs'],
        ];
    }

    public static function occasions(): array
    {
        return [
            ['slug' => 'wedding', 'name' => 'Wedding', 'art' => 'bridal'],
            ['slug' => 'anniversary', 'name' => 'Anniversary', 'art' => 'ring'],
            ['slug' => 'birthday', 'name' => 'Birthday', 'art' => 'pendant'],
            ['slug' => 'engagement', 'name' => 'Engagement', 'art' => 'ring'],
            ['slug' => 'festive', 'name' => 'Festive', 'art' => 'bangle'],
            ['slug' => 'everyday', 'name' => 'Everyday', 'art' => 'chain'],
        ];
    }

    public static function recipients(): array
    {
        return [
            ['slug' => 'for-her', 'name' => 'For Her', 'art' => 'woman', 'blurb' => 'Rings, necklaces & everyday elegance'],
            ['slug' => 'for-him', 'name' => 'For Him', 'art' => 'man', 'blurb' => 'Chains, bands & bold statements'],
        ];
    }

    public static function products(): array
    {
        return [
            [
                'id' => 'eternal-bloom-diamond-ring', 'sku' => 'RG-DM-1042', 'name' => 'Eternal Bloom Diamond Ring',
                'category' => 'rings', 'type' => 'Diamond Ring', 'metal' => 'White Gold', 'purity' => '18K', 'gender' => 'Women',
                'occasion' => ['engagement', 'wedding'], 'collection' => 'diamond-collection', 'art' => 'ring',
                'price' => 45990, 'mrp' => 52990, 'rating' => 4.8, 'reviews_count' => 126,
                'badges' => ['Bestseller'], 'is_new' => false, 'is_bestseller' => true, 'in_stock' => true,
                'short_desc' => 'A floral halo of round brilliant diamonds set in 18K white gold.',
                'description' => 'The Eternal Bloom Diamond Ring draws inspiration from an unfurling flower, with a cluster of round brilliant diamonds haloed around a central stone. Hand-set in 18K white gold, it is finished with a mirror polish for an heirloom-quality shine that catches the light from every angle.',
                'sizes' => ['12', '13', '14', '15', '16', '17', '18'],
                'weight' => ['gross' => '4.20g', 'net' => '3.95g', 'stone' => '0.85g'],
                'diamond' => ['carat' => '0.62 ct', 'colour' => 'VS-EF', 'clarity' => 'VVS1', 'shape' => 'Round Brilliant'],
            ],
            [
                'id' => 'celestial-gold-hoop-earrings', 'sku' => 'ER-GD-2081', 'name' => 'Celestial 18K Gold Earrings',
                'category' => 'earrings', 'type' => 'Gold Hoop Earrings', 'metal' => 'Gold', 'purity' => '18K', 'gender' => 'Women',
                'occasion' => ['everyday', 'festive'], 'collection' => 'everyday-gold', 'art' => 'earring',
                'price' => 32450, 'mrp' => 36990, 'rating' => 4.6, 'reviews_count' => 84,
                'badges' => ['New'], 'is_new' => true, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'Sculptural gold hoops with a brushed-and-polish finish.',
                'description' => 'Inspired by celestial curves, these 18K gold hoops pair a brushed matte texture with polished edges for quiet dimension. Lightweight construction and a secure hinge clasp make them equally suited to a boardroom or a wedding reception.',
                'sizes' => null,
                'weight' => ['gross' => '6.80g', 'net' => '6.80g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'royal-heritage-gold-necklace', 'sku' => 'NK-GD-3399', 'name' => 'Royal Heritage Gold Necklace',
                'category' => 'necklaces', 'type' => 'Temple Gold Necklace', 'metal' => 'Gold', 'purity' => '22K', 'gender' => 'Women',
                'occasion' => ['wedding', 'festive'], 'collection' => 'wedding-collection', 'art' => 'necklace',
                'price' => 124999, 'mrp' => 142500, 'rating' => 4.9, 'reviews_count' => 58,
                'badges' => ['Bestseller'], 'is_new' => false, 'is_bestseller' => true, 'in_stock' => true,
                'short_desc' => 'An intricately crafted temple-inspired statement necklace.',
                'description' => 'A statement piece rooted in South Indian temple artistry, the Royal Heritage Necklace is hand-finished in 22K gold with repoussé detailing. Layers of motif work make it the centrepiece of any bridal or festive ensemble, passed down as a true heirloom.',
                'sizes' => null,
                'weight' => ['gross' => '38.50g', 'net' => '38.50g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'serenity-diamond-pendant', 'sku' => 'PD-DM-1187', 'name' => 'Serenity Diamond Pendant',
                'category' => 'pendants', 'type' => 'Diamond Pendant', 'metal' => 'White Gold', 'purity' => '18K', 'gender' => 'Women',
                'occasion' => ['everyday', 'anniversary'], 'collection' => 'minimal-collection', 'art' => 'pendant',
                'price' => 28750, 'mrp' => 33500, 'rating' => 4.7, 'reviews_count' => 97,
                'badges' => [], 'is_new' => false, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'A single solitaire suspended on a whisper-thin chain.',
                'description' => 'Understated and endlessly wearable, the Serenity Pendant features a solitaire diamond in a four-prong setting suspended from a delicate 18K white gold chain. Designed to layer or wear alone, it is built for everyday radiance.',
                'sizes' => null,
                'weight' => ['gross' => '1.85g', 'net' => '1.65g', 'stone' => '0.20g'],
                'diamond' => ['carat' => '0.30 ct', 'colour' => 'VS-EF', 'clarity' => 'VS1', 'shape' => 'Round Brilliant'],
            ],
            [
                'id' => 'classic-gold-bangle', 'sku' => 'BN-GD-4402', 'name' => 'Classic Gold Bangle',
                'category' => 'bangles', 'type' => 'Gold Kada Bangle', 'metal' => 'Gold', 'purity' => '22K', 'gender' => 'Women',
                'occasion' => ['festive', 'everyday'], 'collection' => 'festive-collection', 'art' => 'bangle',
                'price' => 68990, 'mrp' => 74999, 'rating' => 4.8, 'reviews_count' => 71,
                'badges' => ['Bestseller'], 'is_new' => false, 'is_bestseller' => true, 'in_stock' => true,
                'short_desc' => 'A timeless solid bangle with a hand-engraved border.',
                'description' => 'The Classic Gold Bangle is built for daily wear and generations of use, with a solid 22K gold construction and a hand-engraved geometric border. Its rounded profile sits comfortably on the wrist without catching on sleeves.',
                'sizes' => ['2.4', '2.6', '2.8'],
                'weight' => ['gross' => '18.20g', 'net' => '18.20g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'moonlight-silver-bracelet', 'sku' => 'BR-SL-5561', 'name' => 'Moonlight Silver Bracelet',
                'category' => 'bracelets', 'type' => 'Silver Chain Bracelet', 'metal' => 'Silver', 'purity' => null, 'gender' => 'Women',
                'occasion' => ['everyday', 'birthday'], 'collection' => null, 'art' => 'bracelet',
                'price' => 5499, 'mrp' => 6999, 'rating' => 4.5, 'reviews_count' => 143,
                'badges' => ['New'], 'is_new' => true, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => '925 sterling silver with a fine box-chain link.',
                'description' => 'A refined everyday layer, the Moonlight Bracelet is crafted in 925 sterling silver with a fine box-chain link and rhodium plating to resist tarnish. Adjustable clasp fits most wrist sizes.',
                'sizes' => ['Adjustable'],
                'weight' => ['gross' => '4.10g', 'net' => '4.10g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'infinity-diamond-tennis-bracelet', 'sku' => 'BR-DM-5602', 'name' => 'Infinity Diamond Tennis Bracelet',
                'category' => 'bracelets', 'type' => 'Diamond Tennis Bracelet', 'metal' => 'White Gold', 'purity' => '18K', 'gender' => 'Women',
                'occasion' => ['anniversary', 'wedding'], 'collection' => 'diamond-collection', 'art' => 'bracelet',
                'price' => 189000, 'mrp' => 214000, 'rating' => 4.9, 'reviews_count' => 39,
                'badges' => ['Bestseller'], 'is_new' => false, 'is_bestseller' => true, 'in_stock' => true,
                'short_desc' => 'A continuous line of brilliant-cut diamonds in a secure box setting.',
                'description' => 'A continuous line of forty round brilliant diamonds set in a secure box setting, finished with a hidden safety clasp. The Infinity Bracelet is a timeless investment piece suited to both celebration and everyday luxury.',
                'sizes' => ['6.5 in', '7 in', '7.5 in'],
                'weight' => ['gross' => '9.40g', 'net' => '7.10g', 'stone' => '2.30g'],
                'diamond' => ['carat' => '3.10 ct', 'colour' => 'VS-EF', 'clarity' => 'VVS2', 'shape' => 'Round Brilliant'],
            ],
            [
                'id' => 'heirloom-gold-chain', 'sku' => 'CH-GD-6120', 'name' => 'Heirloom Gold Rope Chain',
                'category' => 'chains', 'type' => 'Gold Rope Chain', 'metal' => 'Gold', 'purity' => '22K', 'gender' => 'Unisex',
                'occasion' => ['everyday', 'festive'], 'collection' => 'everyday-gold', 'art' => 'chain',
                'price' => 54250, 'mrp' => 59900, 'rating' => 4.7, 'reviews_count' => 65,
                'badges' => [], 'is_new' => false, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'A dense rope-link chain with a lobster clasp.',
                'description' => 'Densely woven rope links in 22K gold give this chain substantial presence without excess weight. Finished with a secure lobster clasp, it wears equally well solo or layered with pendants.',
                'sizes' => ['18 in', '20 in', '22 in', '24 in'],
                'weight' => ['gross' => '12.60g', 'net' => '12.60g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'monarch-mens-gold-chain', 'sku' => 'CH-GD-6188', 'name' => 'Monarch Men\'s Gold Curb Chain',
                'category' => 'mens', 'type' => 'Gold Curb Chain', 'metal' => 'Gold', 'purity' => '22K', 'gender' => 'Men',
                'occasion' => ['everyday', 'festive'], 'collection' => null, 'art' => 'mens',
                'price' => 89500, 'mrp' => 97900, 'rating' => 4.6, 'reviews_count' => 41,
                'badges' => [], 'is_new' => false, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'A bold curb-link chain in solid 22K gold.',
                'description' => 'Built with a heavier gauge for a commanding presence, the Monarch Curb Chain is finished with a diamond-cut edge that catches light along every link. A secure box clasp keeps it in place through the day.',
                'sizes' => ['20 in', '22 in', '24 in'],
                'weight' => ['gross' => '22.40g', 'net' => '22.40g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'signet-mens-diamond-ring', 'sku' => 'RG-DM-1099', 'name' => 'Signet Men\'s Diamond Ring',
                'category' => 'mens', 'type' => 'Diamond Signet Ring', 'metal' => 'Gold', 'purity' => '18K', 'gender' => 'Men',
                'occasion' => ['everyday', 'wedding'], 'collection' => null, 'art' => 'ring',
                'price' => 62990, 'mrp' => 69990, 'rating' => 4.7, 'reviews_count' => 33,
                'badges' => ['New'], 'is_new' => true, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'A brushed-finish signet with three bezel-set diamonds.',
                'description' => 'A modern take on the classic signet, finished with a brushed matte face and three flush bezel-set diamonds. Solid 18K gold construction gives it durability for daily wear.',
                'sizes' => ['19', '20', '21', '22', '23'],
                'weight' => ['gross' => '9.80g', 'net' => '9.35g', 'stone' => '0.45g'],
                'diamond' => ['carat' => '0.24 ct', 'colour' => 'VS-FG', 'clarity' => 'VS2', 'shape' => 'Round Brilliant'],
            ],
            [
                'id' => 'aurora-diamond-studs', 'sku' => 'ER-DM-2144', 'name' => 'Aurora Diamond Stud Earrings',
                'category' => 'earrings', 'type' => 'Diamond Studs', 'metal' => 'White Gold', 'purity' => '18K', 'gender' => 'Women',
                'occasion' => ['everyday', 'anniversary'], 'collection' => 'diamond-collection', 'art' => 'earring',
                'price' => 41200, 'mrp' => 46500, 'rating' => 4.9, 'reviews_count' => 158,
                'badges' => ['Bestseller'], 'is_new' => false, 'is_bestseller' => true, 'in_stock' => true,
                'short_desc' => 'Timeless four-prong diamond solitaires.',
                'description' => 'A jewellery box essential, the Aurora Studs feature two matched round brilliant diamonds in a classic four-prong setting with secure screw backs. Built to wear every single day.',
                'sizes' => null,
                'weight' => ['gross' => '1.40g', 'net' => '1.10g', 'stone' => '0.30g'],
                'diamond' => ['carat' => '0.50 ct tw', 'colour' => 'VS-EF', 'clarity' => 'VVS2', 'shape' => 'Round Brilliant'],
            ],
            [
                'id' => 'blossom-rose-gold-earrings', 'sku' => 'ER-RG-2210', 'name' => 'Blossom Rose Gold Drop Earrings',
                'category' => 'earrings', 'type' => 'Rose Gold Drop Earrings', 'metal' => 'Rose Gold', 'purity' => '14K', 'gender' => 'Women',
                'occasion' => ['birthday', 'everyday'], 'collection' => 'minimal-collection', 'art' => 'earring',
                'price' => 24990, 'mrp' => 27990, 'rating' => 4.5, 'reviews_count' => 52,
                'badges' => [], 'is_new' => false, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'Petal-shaped drops in warm 14K rose gold.',
                'description' => 'Soft petal silhouettes in warm 14K rose gold move gently with every turn of the head. A secure friction back makes them comfortable for all-day wear.',
                'sizes' => null,
                'weight' => ['gross' => '3.60g', 'net' => '3.60g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'lumiere-diamond-necklace', 'sku' => 'NK-DM-3410', 'name' => 'Lumiere Diamond Necklace',
                'category' => 'necklaces', 'type' => 'Diamond Station Necklace', 'metal' => 'White Gold', 'purity' => '18K', 'gender' => 'Women',
                'occasion' => ['anniversary', 'wedding'], 'collection' => 'diamond-collection', 'art' => 'necklace',
                'price' => 96500, 'mrp' => 108900, 'rating' => 4.8, 'reviews_count' => 47,
                'badges' => [], 'is_new' => false, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'Five floating diamonds along a fine 18K chain.',
                'description' => 'Five bezel-set diamonds float along a fine cable chain, catching light with every movement. The Lumiere Necklace is designed to be worn alone as a refined everyday statement.',
                'sizes' => ['16 in', '18 in'],
                'weight' => ['gross' => '3.20g', 'net' => '2.85g', 'stone' => '0.35g'],
                'diamond' => ['carat' => '0.75 ct tw', 'colour' => 'VS-EF', 'clarity' => 'VS1', 'shape' => 'Round Brilliant'],
            ],
            [
                'id' => 'everyday-gold-chain-necklace', 'sku' => 'NK-GD-3455', 'name' => 'Everyday Fine Gold Chain',
                'category' => 'necklaces', 'type' => 'Fine Gold Chain', 'metal' => 'Gold', 'purity' => '14K', 'gender' => 'Women',
                'occasion' => ['everyday'], 'collection' => 'everyday-gold', 'art' => 'chain',
                'price' => 18990, 'mrp' => 21500, 'rating' => 4.6, 'reviews_count' => 112,
                'badges' => ['Bestseller'], 'is_new' => false, 'is_bestseller' => true, 'in_stock' => true,
                'short_desc' => 'A hair-fine cable chain built for daily layering.',
                'description' => 'The essential layering chain, finished in 14K gold with a barely-there cable link. Wear it alone or stack it with your favourite pendants.',
                'sizes' => ['16 in', '18 in', '20 in'],
                'weight' => ['gross' => '2.10g', 'net' => '2.10g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'bridal-kundan-choker-set', 'sku' => 'BD-GD-9001', 'name' => 'Meherangarh Kundan Bridal Set',
                'category' => 'bridal', 'type' => 'Kundan Choker Set', 'metal' => 'Gold', 'purity' => '22K', 'gender' => 'Women',
                'occasion' => ['wedding'], 'collection' => 'wedding-collection', 'art' => 'bridal',
                'price' => 248000, 'mrp' => 279000, 'rating' => 5.0, 'reviews_count' => 22,
                'badges' => ['Limited'], 'is_new' => false, 'is_bestseller' => true, 'in_stock' => true,
                'short_desc' => 'A complete choker, earring & maang tikka bridal set.',
                'description' => 'A complete bridal ensemble featuring a Kundan-set choker, matching drop earrings and a maang tikka, hand-finished in 22K gold with meenakari detailing on the reverse. Made to order with a 3-week crafting timeline.',
                'sizes' => null,
                'weight' => ['gross' => '86.40g', 'net' => '86.40g', 'stone' => '4.20g'],
                'diamond' => null,
            ],
            [
                'id' => 'gulzar-polki-necklace-set', 'sku' => 'BD-DM-9044', 'name' => 'Gulzar Polki Diamond Necklace Set',
                'category' => 'bridal', 'type' => 'Polki Diamond Necklace Set', 'metal' => 'Gold', 'purity' => '18K', 'gender' => 'Women',
                'occasion' => ['wedding', 'festive'], 'collection' => 'wedding-collection', 'art' => 'bridal',
                'price' => 412000, 'mrp' => 459000, 'rating' => 4.9, 'reviews_count' => 14,
                'badges' => ['Limited'], 'is_new' => false, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'Uncut polki diamonds set in a layered gold necklace.',
                'description' => 'Uncut polki diamonds are hand-set across a layered 18K gold necklace with matching jhumka earrings, finished with a ruby and emerald accent border in traditional Jaipuri style.',
                'sizes' => null,
                'weight' => ['gross' => '64.80g', 'net' => '64.80g', 'stone' => '8.60g'],
                'diamond' => ['carat' => '6.40 ct', 'colour' => 'Natural Polki', 'clarity' => 'Uncut', 'shape' => 'Polki'],
            ],
            [
                'id' => 'petal-silver-stud-earrings', 'sku' => 'ER-SL-2299', 'name' => 'Petal Silver Stud Earrings',
                'category' => 'earrings', 'type' => 'Silver Stud Earrings', 'metal' => 'Silver', 'purity' => null, 'gender' => 'Women',
                'occasion' => ['everyday', 'birthday'], 'collection' => null, 'art' => 'earring',
                'price' => 2899, 'mrp' => 3499, 'rating' => 4.4, 'reviews_count' => 201,
                'badges' => ['Bestseller'], 'is_new' => false, 'is_bestseller' => true, 'in_stock' => true,
                'short_desc' => 'Minimal petal studs in 925 sterling silver.',
                'description' => 'Small enough for everyday wear, these petal-shaped studs are crafted in 925 sterling silver with rhodium plating for lasting shine.',
                'sizes' => null,
                'weight' => ['gross' => '1.20g', 'net' => '1.20g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'linear-silver-pendant', 'sku' => 'PD-SL-1233', 'name' => 'Linear Bar Silver Pendant',
                'category' => 'pendants', 'type' => 'Silver Bar Pendant', 'metal' => 'Silver', 'purity' => null, 'gender' => 'Unisex',
                'occasion' => ['everyday'], 'collection' => 'minimal-collection', 'art' => 'pendant',
                'price' => 3299, 'mrp' => 3999, 'rating' => 4.3, 'reviews_count' => 66,
                'badges' => ['New'], 'is_new' => true, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'A minimalist bar pendant on a fine silver chain.',
                'description' => 'A clean architectural line in polished 925 sterling silver, hung from a fine box chain. Understated enough to wear daily, striking enough to notice.',
                'sizes' => ['18 in'],
                'weight' => ['gross' => '2.40g', 'net' => '2.40g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'twin-halo-diamond-ring', 'sku' => 'RG-DM-1150', 'name' => 'Twin Halo Diamond Engagement Ring',
                'category' => 'rings', 'type' => 'Diamond Engagement Ring', 'metal' => 'Platinum', 'purity' => null, 'gender' => 'Women',
                'occasion' => ['engagement'], 'collection' => 'diamond-collection', 'art' => 'ring',
                'price' => 285000, 'mrp' => 318000, 'rating' => 4.9, 'reviews_count' => 28,
                'badges' => ['Bestseller'], 'is_new' => false, 'is_bestseller' => true, 'in_stock' => true,
                'short_desc' => 'Two interlocking diamond halos in platinum.',
                'description' => 'Two interlocking halos of pave-set diamonds frame a central round brilliant stone, set in platinum for maximum durability and shine. A modern heirloom for a lifetime of wear.',
                'sizes' => ['11', '12', '13', '14', '15', '16'],
                'weight' => ['gross' => '5.10g', 'net' => '4.60g', 'stone' => '0.95g'],
                'diamond' => ['carat' => '1.20 ct', 'colour' => 'VVS-DE', 'clarity' => 'VVS1', 'shape' => 'Round Brilliant'],
            ],
            [
                'id' => 'stacking-gold-band', 'sku' => 'RG-GD-1211', 'name' => 'Stacking Gold Band',
                'category' => 'rings', 'type' => 'Gold Stacking Band', 'metal' => 'Gold', 'purity' => '18K', 'gender' => 'Women',
                'occasion' => ['everyday', 'birthday'], 'collection' => 'minimal-collection', 'art' => 'ring',
                'price' => 14990, 'mrp' => 16990, 'rating' => 4.6, 'reviews_count' => 174,
                'badges' => [], 'is_new' => false, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'A slim, comfort-fit band designed for stacking.',
                'description' => 'A slim 2mm comfort-fit band in 18K gold, designed to be worn solo or stacked three-deep. Rounded edges make it comfortable for all-day, every-day wear.',
                'sizes' => ['10', '11', '12', '13', '14', '15', '16', '17'],
                'weight' => ['gross' => '2.30g', 'net' => '2.30g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'cascade-gold-bangle-set', 'sku' => 'BN-GD-4460', 'name' => 'Cascade Gold Bangle Set of 2',
                'category' => 'bangles', 'type' => 'Gold Bangle Set', 'metal' => 'Gold', 'purity' => '22K', 'gender' => 'Women',
                'occasion' => ['festive', 'wedding'], 'collection' => 'festive-collection', 'art' => 'bangle',
                'price' => 118500, 'mrp' => 129900, 'rating' => 4.8, 'reviews_count' => 36,
                'badges' => [], 'is_new' => false, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'A matched pair with cascading filigree work.',
                'description' => 'Sold as a matched pair, the Cascade Bangles feature cascading filigree work in 22K gold, designed to be worn together or separately across multiple occasions.',
                'sizes' => ['2.4', '2.6', '2.8'],
                'weight' => ['gross' => '31.20g', 'net' => '31.20g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'orbit-diamond-bangle', 'sku' => 'BN-DM-4488', 'name' => 'Orbit Diamond Bangle',
                'category' => 'bangles', 'type' => 'Diamond Bangle', 'metal' => 'White Gold', 'purity' => '18K', 'gender' => 'Women',
                'occasion' => ['anniversary', 'festive'], 'collection' => 'diamond-collection', 'art' => 'bangle',
                'price' => 156000, 'mrp' => 172000, 'rating' => 4.7, 'reviews_count' => 19,
                'badges' => ['New'], 'is_new' => true, 'is_bestseller' => false, 'in_stock' => false,
                'short_desc' => 'A hinged bangle set with a continuous line of diamonds.',
                'description' => 'A hinged bangle set with a continuous line of channel-set diamonds, finished with a concealed box clasp for a seamless look when worn.',
                'sizes' => ['2.4', '2.6'],
                'weight' => ['gross' => '14.80g', 'net' => '12.90g', 'stone' => '1.90g'],
                'diamond' => ['carat' => '1.85 ct tw', 'colour' => 'VS-EF', 'clarity' => 'VS1', 'shape' => 'Round Brilliant'],
            ],
            [
                'id' => 'anchor-silver-chain-mens', 'sku' => 'CH-SL-6244', 'name' => "Anchor Men's Silver Chain",
                'category' => 'mens', 'type' => 'Silver Curb Chain', 'metal' => 'Silver', 'purity' => null, 'gender' => 'Men',
                'occasion' => ['everyday'], 'collection' => null, 'art' => 'mens',
                'price' => 8990, 'mrp' => 10500, 'rating' => 4.5, 'reviews_count' => 58,
                'badges' => [], 'is_new' => false, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'A heavy-gauge curb chain in oxidised silver.',
                'description' => 'A heavy-gauge curb chain in 925 silver with a subtle oxidised finish that highlights each link. Fitted with a durable lobster clasp.',
                'sizes' => ['20 in', '22 in', '24 in'],
                'weight' => ['gross' => '18.60g', 'net' => '18.60g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'velora-diamond-hoops', 'sku' => 'ER-DM-2266', 'name' => 'Velora Diamond Hoop Earrings',
                'category' => 'earrings', 'type' => 'Diamond Hoops', 'metal' => 'White Gold', 'purity' => '18K', 'gender' => 'Women',
                'occasion' => ['everyday', 'anniversary'], 'collection' => 'diamond-collection', 'art' => 'earring',
                'price' => 58900, 'mrp' => 65900, 'rating' => 4.8, 'reviews_count' => 44,
                'badges' => [], 'is_new' => false, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'Pave diamond hoops in a huggie silhouette.',
                'description' => 'A huggie silhouette fully pave-set with round brilliant diamonds, designed to hug the earlobe for a comfortable, secure fit through long days and late nights.',
                'sizes' => null,
                'weight' => ['gross' => '5.60g', 'net' => '4.80g', 'stone' => '0.80g'],
                'diamond' => ['carat' => '0.95 ct tw', 'colour' => 'VS-EF', 'clarity' => 'VS2', 'shape' => 'Round Brilliant'],
            ],
            [
                'id' => 'heritage-silver-bangle', 'sku' => 'BN-SL-4510', 'name' => 'Heritage Oxidised Silver Bangle',
                'category' => 'bangles', 'type' => 'Oxidised Silver Bangle', 'metal' => 'Silver', 'purity' => null, 'gender' => 'Women',
                'occasion' => ['festive', 'everyday'], 'collection' => null, 'art' => 'bangle',
                'price' => 6499, 'mrp' => 7499, 'rating' => 4.4, 'reviews_count' => 89,
                'badges' => [], 'is_new' => false, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'Hand-etched tribal motifs in oxidised silver.',
                'description' => 'Hand-etched tribal motifs cover this solid oxidised silver bangle, offering a bold textured look that pairs beautifully with both ethnic and contemporary outfits.',
                'sizes' => ['2.4', '2.6', '2.8'],
                'weight' => ['gross' => '22.00g', 'net' => '22.00g', 'stone' => '0g'],
                'diamond' => null,
            ],
            [
                'id' => 'solitaire-promise-ring', 'sku' => 'RG-DM-1188', 'name' => 'Solitaire Promise Ring',
                'category' => 'rings', 'type' => 'Diamond Promise Ring', 'metal' => 'Rose Gold', 'purity' => '14K', 'gender' => 'Women',
                'occasion' => ['anniversary', 'birthday'], 'collection' => 'minimal-collection', 'art' => 'ring',
                'price' => 21990, 'mrp' => 24990, 'rating' => 4.6, 'reviews_count' => 96,
                'badges' => ['New'], 'is_new' => true, 'is_bestseller' => false, 'in_stock' => true,
                'short_desc' => 'A single solitaire on a slim 14K rose gold band.',
                'description' => 'A single 4-prong solitaire diamond sits atop a slim, comfort-fit 14K rose gold band — a quiet everyday reminder rather than a statement piece.',
                'sizes' => ['10', '11', '12', '13', '14', '15'],
                'weight' => ['gross' => '1.90g', 'net' => '1.75g', 'stone' => '0.15g'],
                'diamond' => ['carat' => '0.18 ct', 'colour' => 'VS-FG', 'clarity' => 'VS2', 'shape' => 'Round Brilliant'],
            ],
        ];
    }

    public static function product(string $id): ?array
    {
        return collect(self::products())->firstWhere('id', $id);
    }

    public static function byCategory(string $slug): array
    {
        if ($slug === 'new-arrivals') {
            return array_values(array_filter(self::products(), fn ($p) => $p['is_new']));
        }
        if ($slug === 'best-sellers') {
            return array_values(array_filter(self::products(), fn ($p) => $p['is_bestseller']));
        }

        return array_values(array_filter(self::products(), function ($p) use ($slug) {
            if ($p['category'] === $slug) {
                return true;
            }
            if (in_array($slug, ['gold', 'silver', 'diamond'], true)) {
                return str_contains(strtolower($p['metal']), $slug === 'diamond' ? '' : $slug)
                    || ($slug === 'diamond' && ! empty($p['diamond']));
            }

            return false;
        }));
    }

    public static function byCollection(string $slug): array
    {
        return array_values(array_filter(self::products(), fn ($p) => $p['collection'] === $slug));
    }

    public static function newArrivals(): array
    {
        return array_values(array_filter(self::products(), fn ($p) => $p['is_new']));
    }

    public static function bestSellers(): array
    {
        return array_values(array_filter(self::products(), fn ($p) => $p['is_bestseller']));
    }

    public static function related(string $id, int $limit = 4): array
    {
        $product = self::product($id);
        if (! $product) {
            return [];
        }

        return collect(self::products())
            ->where('id', '!=', $id)
            ->filter(fn ($p) => $p['category'] === $product['category'])
            ->take($limit)
            ->values()
            ->all();
    }

    public static function reviews(): array
    {
        return [
            ['name' => 'Ananya Rao', 'location' => 'Bengaluru', 'rating' => 5, 'verified' => true, 'date' => 'Aug 2026', 'text' => 'The ring exceeded every expectation — the craftsmanship is impeccable and it photographs even better in person. Packaging felt genuinely luxurious.'],
            ['name' => 'Karthik Menon', 'location' => 'Chennai', 'rating' => 5, 'verified' => true, 'date' => 'Jul 2026', 'text' => 'Bought the curb chain as a gift for my father. The finish and weight feel premium, and the certification gave us full confidence in the purity.'],
            ['name' => 'Priya Nair', 'location' => 'Kochi', 'rating' => 4, 'verified' => true, 'date' => 'Jul 2026', 'text' => 'Gorgeous earrings, exactly as pictured. Delivery was a day earlier than promised. Only wish there were more size options.'],
            ['name' => 'Rohan Kapoor', 'location' => 'Delhi', 'rating' => 5, 'verified' => true, 'date' => 'Jun 2026', 'text' => 'This was our second bridal order from them. The Kundan set was even more detailed than the previous piece. Worth every rupee.'],
            ['name' => 'Meera Iyer', 'location' => 'Mumbai', 'rating' => 5, 'verified' => true, 'date' => 'Jun 2026', 'text' => 'The return process for a size exchange was refreshingly simple. Customer support was responsive throughout. Will shop again.'],
            ['name' => 'Aditya Sharma', 'location' => 'Pune', 'rating' => 4, 'verified' => true, 'date' => 'May 2026', 'text' => 'Elegant design, solid weight for the price point. Shipping took a couple of days longer than expected but the piece made up for it.'],
            ['name' => 'Divya Krishnan', 'location' => 'Hyderabad', 'rating' => 5, 'verified' => true, 'date' => 'May 2026', 'text' => 'The diamond pendant is my new everyday piece. Comfortable, doesn\'t tarnish, and the chain length is perfect.'],
            ['name' => 'Vikram Singh', 'location' => 'Jaipur', 'rating' => 5, 'verified' => true, 'date' => 'Apr 2026', 'text' => 'Impressed by the certification transparency — every detail about the diamonds was documented clearly. Feels like a trustworthy brand.'],
        ];
    }

    public static function faqs(): array
    {
        return [
            ['q' => 'Is your jewellery certified?', 'a' => 'Yes, all our diamond and gold jewellery comes with certification from recognised authorities such as IGI and BIS hallmarking for gold purity.'],
            ['q' => 'What is your return policy?', 'a' => 'We offer a 15-day easy return window from the date of delivery, provided the item is unused and in its original packaging with all certificates.'],
            ['q' => 'Do you offer free shipping?', 'a' => 'Yes, we offer free insured shipping on all orders above ₹2,999. Orders below this amount incur a flat shipping fee shown at checkout.'],
            ['q' => 'Can I customise a ring size after ordering?', 'a' => 'Yes, one complimentary resizing is available within 30 days of delivery. Please raise a request via your account or contact support.'],
            ['q' => 'What payment methods do you accept?', 'a' => 'We accept UPI, all major credit and debit cards, net banking, popular wallets, and cash on delivery on eligible orders.'],
        ];
    }

    public static function addresses(): array
    {
        return [
            ['id' => 1, 'type' => 'Home', 'default' => true, 'name' => 'Ananya Rao', 'phone' => '+91 98765 43210', 'line1' => '14, Lavender Residency', 'line2' => 'Indiranagar 100 Feet Road', 'landmark' => 'Near Sony World Signal', 'city' => 'Bengaluru', 'state' => 'Karnataka', 'pincode' => '560038', 'country' => 'India'],
            ['id' => 2, 'type' => 'Office', 'default' => false, 'name' => 'Ananya Rao', 'phone' => '+91 98765 43210', 'line1' => 'WeWork Prestige Atlanta', 'line2' => 'Koramangala 4th Block', 'landmark' => 'Opp. Forum Mall', 'city' => 'Bengaluru', 'state' => 'Karnataka', 'pincode' => '560034', 'country' => 'India'],
            ['id' => 3, 'type' => 'Other', 'default' => false, 'name' => 'Kavya Rao', 'phone' => '+91 91234 56789', 'line1' => '22, Green Meadows Apartments', 'line2' => 'JP Nagar 7th Phase', 'landmark' => 'Near Sarakki Lake', 'city' => 'Bengaluru', 'state' => 'Karnataka', 'pincode' => '560078', 'country' => 'India'],
        ];
    }

    public static function orders(): array
    {
        $p = fn ($id) => self::product($id);

        return [
            [
                'id' => 'ORD-2026-10482', 'date' => '02 Sep 2026', 'status' => 'Delivered', 'payment_status' => 'Paid', 'payment_method' => 'UPI',
                'items' => [['product' => $p('eternal-bloom-diamond-ring'), 'qty' => 1, 'size' => '14']],
                'total' => 45990, 'address' => self::addresses()[0],
                'timeline' => ['Order Placed' => '02 Sep 2026', 'Confirmed' => '02 Sep 2026', 'Packed' => '03 Sep 2026', 'Shipped' => '04 Sep 2026', 'Out for Delivery' => '06 Sep 2026', 'Delivered' => '06 Sep 2026'],
            ],
            [
                'id' => 'ORD-2026-10399', 'date' => '24 Aug 2026', 'status' => 'Shipped', 'payment_status' => 'Paid', 'payment_method' => 'Credit Card',
                'items' => [['product' => $p('celestial-gold-hoop-earrings'), 'qty' => 1, 'size' => null]],
                'total' => 32450, 'address' => self::addresses()[0],
                'timeline' => ['Order Placed' => '24 Aug 2026', 'Confirmed' => '24 Aug 2026', 'Packed' => '25 Aug 2026', 'Shipped' => '26 Aug 2026', 'Out for Delivery' => null, 'Delivered' => null],
            ],
            [
                'id' => 'ORD-2026-10287', 'date' => '10 Aug 2026', 'status' => 'Processing', 'payment_status' => 'Paid', 'payment_method' => 'Net Banking',
                'items' => [['product' => $p('moonlight-silver-bracelet'), 'qty' => 2, 'size' => 'Adjustable']],
                'total' => 10998, 'address' => self::addresses()[1],
                'timeline' => ['Order Placed' => '10 Aug 2026', 'Confirmed' => '10 Aug 2026', 'Packed' => null, 'Shipped' => null, 'Out for Delivery' => null, 'Delivered' => null],
            ],
            [
                'id' => 'ORD-2026-10145', 'date' => '22 Jul 2026', 'status' => 'Cancelled', 'payment_status' => 'Refunded', 'payment_method' => 'UPI',
                'items' => [['product' => $p('stacking-gold-band'), 'qty' => 1, 'size' => '13']],
                'total' => 14990, 'address' => self::addresses()[0],
                'timeline' => ['Order Placed' => '22 Jul 2026', 'Confirmed' => '22 Jul 2026', 'Packed' => null, 'Shipped' => null, 'Out for Delivery' => null, 'Delivered' => null],
            ],
            [
                'id' => 'ORD-2026-09988', 'date' => '30 Jun 2026', 'status' => 'Returned', 'payment_status' => 'Refunded', 'payment_method' => 'Debit Card',
                'items' => [['product' => $p('petal-silver-stud-earrings'), 'qty' => 1, 'size' => null]],
                'total' => 2899, 'address' => self::addresses()[0],
                'timeline' => ['Order Placed' => '30 Jun 2026', 'Confirmed' => '30 Jun 2026', 'Packed' => '01 Jul 2026', 'Shipped' => '02 Jul 2026', 'Out for Delivery' => '04 Jul 2026', 'Delivered' => '04 Jul 2026'],
            ],
            [
                'id' => 'ORD-2026-09850', 'date' => '12 Jun 2026', 'status' => 'Delivered', 'payment_status' => 'Paid', 'payment_method' => 'COD',
                'items' => [['product' => $p('everyday-gold-chain-necklace'), 'qty' => 1, 'size' => '18 in'], ['product' => $p('linear-silver-pendant'), 'qty' => 1, 'size' => '18 in']],
                'total' => 22289, 'address' => self::addresses()[2],
                'timeline' => ['Order Placed' => '12 Jun 2026', 'Confirmed' => '12 Jun 2026', 'Packed' => '13 Jun 2026', 'Shipped' => '14 Jun 2026', 'Out for Delivery' => '16 Jun 2026', 'Delivered' => '16 Jun 2026'],
            ],
        ];
    }

    public static function order(string $id): ?array
    {
        return collect(self::orders())->firstWhere('id', $id);
    }
}

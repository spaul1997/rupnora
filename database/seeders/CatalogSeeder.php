<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::query()->exists()) {
            return;
        }

        $products = [
            ['name' => 'Eternal Bloom Diamond Ring', 'cat' => 'Solitaire Ring', 'type' => 'Ring', 'metal' => 'White Gold', 'purity' => '18K', 'mrp' => 52990, 'price' => 45990, 'stock' => 12, 'diamond' => true, 'featured' => true, 'best' => true, 'sizes' => ['12', '13', '14', '15', '16']],
            ['name' => 'Twin Halo Diamond Engagement Ring', 'cat' => 'Couple Ring', 'type' => 'Ring', 'metal' => 'Platinum', 'purity' => null, 'mrp' => 318000, 'price' => 285000, 'stock' => 4, 'diamond' => true, 'featured' => true, 'best' => true, 'sizes' => ['11', '12', '13', '14']],
            ['name' => 'Stacking Gold Band', 'cat' => 'Band Ring', 'type' => 'Ring', 'metal' => 'Gold', 'purity' => '18K', 'mrp' => 16990, 'price' => 14990, 'stock' => 25, 'diamond' => false, 'featured' => false, 'best' => false, 'sizes' => ['10', '11', '12', '13', '14']],
            ['name' => 'Celestial 18K Gold Hoop Earrings', 'cat' => 'Hoop', 'type' => 'Earrings', 'metal' => 'Gold', 'purity' => '18K', 'mrp' => 36990, 'price' => 32450, 'stock' => 8, 'diamond' => false, 'featured' => false, 'best' => false, 'sizes' => null],
            ['name' => 'Aurora Diamond Stud Earrings', 'cat' => 'Stud', 'type' => 'Earrings', 'metal' => 'White Gold', 'purity' => '18K', 'mrp' => 46500, 'price' => 41200, 'stock' => 3, 'diamond' => true, 'featured' => true, 'best' => true, 'sizes' => null],
            ['name' => 'Blossom Rose Gold Drop Earrings', 'cat' => 'Drop', 'type' => 'Earrings', 'metal' => 'Rose Gold', 'purity' => '14K', 'mrp' => 27990, 'price' => 24990, 'stock' => 15, 'diamond' => false, 'featured' => false, 'best' => false, 'sizes' => null],
            ['name' => 'Royal Heritage Gold Necklace', 'cat' => 'Necklaces', 'type' => 'Necklace', 'metal' => 'Gold', 'purity' => '22K', 'mrp' => 142500, 'price' => 124999, 'stock' => 2, 'diamond' => false, 'featured' => true, 'best' => true, 'sizes' => null],
            ['name' => 'Lumiere Diamond Necklace', 'cat' => 'Necklaces', 'type' => 'Necklace', 'metal' => 'White Gold', 'purity' => '18K', 'mrp' => 108900, 'price' => 96500, 'stock' => 5, 'diamond' => true, 'featured' => false, 'best' => false, 'sizes' => null],
            ['name' => 'Serenity Diamond Pendant', 'cat' => 'Pendants', 'type' => 'Pendant', 'metal' => 'White Gold', 'purity' => '18K', 'mrp' => 33500, 'price' => 28750, 'stock' => 18, 'diamond' => true, 'featured' => false, 'best' => false, 'sizes' => null],
            ['name' => 'Classic Gold Bangle', 'cat' => 'Bangle Set', 'type' => 'Bangle', 'metal' => 'Gold', 'purity' => '22K', 'mrp' => 74999, 'price' => 68990, 'stock' => 6, 'diamond' => false, 'featured' => true, 'best' => true, 'sizes' => ['2.4', '2.6', '2.8']],
            ['name' => 'Moonlight Silver Bracelet', 'cat' => 'Bracelets', 'type' => 'Bracelet', 'metal' => 'Silver', 'purity' => null, 'mrp' => 6999, 'price' => 5499, 'stock' => 40, 'diamond' => false, 'featured' => false, 'best' => false, 'sizes' => ['Adjustable']],
            ['name' => 'Infinity Diamond Tennis Bracelet', 'cat' => 'Bracelets', 'type' => 'Bracelet', 'metal' => 'White Gold', 'purity' => '18K', 'mrp' => 214000, 'price' => 189000, 'stock' => 2, 'diamond' => true, 'featured' => true, 'best' => true, 'sizes' => ['6.5 in', '7 in', '7.5 in']],
            ['name' => 'Heirloom Gold Rope Chain', 'cat' => 'Chain Necklace', 'type' => 'Chain', 'metal' => 'Gold', 'purity' => '22K', 'mrp' => 59900, 'price' => 54250, 'stock' => 9, 'diamond' => false, 'featured' => false, 'best' => false, 'sizes' => ['18 in', '20 in', '22 in']],
            ['name' => "Monarch Men's Gold Curb Chain", 'cat' => 'Chain Necklace', 'type' => 'Chain', 'metal' => 'Gold', 'purity' => '22K', 'mrp' => 97900, 'price' => 89500, 'stock' => 7, 'diamond' => false, 'featured' => false, 'best' => false, 'sizes' => ['20 in', '22 in', '24 in']],
            ['name' => "Signet Men's Diamond Ring", 'cat' => 'Statement Ring', 'type' => 'Ring', 'metal' => 'Gold', 'purity' => '18K', 'mrp' => 69990, 'price' => 62990, 'stock' => 0, 'diamond' => true, 'featured' => false, 'best' => false, 'sizes' => ['19', '20', '21', '22']],
            ['name' => 'Meherangarh Kundan Bridal Set', 'cat' => 'Complete Jewellery Set', 'type' => 'Necklace', 'metal' => 'Gold', 'purity' => '22K', 'mrp' => 279000, 'price' => 248000, 'stock' => 1, 'diamond' => false, 'featured' => true, 'best' => true, 'sizes' => null],
        ];

        $productModels = [];

        foreach ($products as $i => $data) {
            $category = $this->categoryFor($data['cat']);
            $sku = 'AUR-'.strtoupper(Str::random(2)).'-'.str_pad((string) ($i + 1001), 4, '0', STR_PAD_LEFT);

            $product = Product::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'sku' => $sku,
                'barcode' => (string) random_int(1000000000000, 9999999999999),
                'category_id' => $category->id,
                'brand' => 'Aurelle',
                'short_description' => "A beautifully crafted {$data['type']} finished in {$data['metal']}.",
                'description' => "The {$data['name']} is hand-finished by our master artisans, combining timeless design with certified quality craftsmanship. Perfect for gifting or everyday elegance.",
                'jewellery_type' => $data['type'],
                'metal_type' => $data['metal'],
                'metal_colour' => str_contains($data['metal'], 'Rose') ? 'Rose' : (str_contains($data['metal'], 'White') ? 'White' : 'Yellow'),
                'purity' => $data['purity'],
                'gross_weight' => round(mt_rand(15, 450) / 10, 2),
                'net_weight' => round(mt_rand(10, 400) / 10, 2),
                'metal_weight' => round(mt_rand(10, 400) / 10, 2),
                'has_diamond' => $data['diamond'],
                'diamond_carat' => $data['diamond'] ? round(mt_rand(20, 300) / 100, 2) : null,
                'diamond_colour' => $data['diamond'] ? 'VS-EF' : null,
                'diamond_clarity' => $data['diamond'] ? 'VVS1' : null,
                'diamond_cut' => $data['diamond'] ? 'Excellent' : null,
                'diamond_shape' => $data['diamond'] ? 'Round Brilliant' : null,
                'diamond_count' => $data['diamond'] ? mt_rand(1, 40) : null,
                'has_gemstone' => false,
                'mrp' => $data['mrp'],
                'selling_price' => $data['price'],
                'offer_price' => null,
                'discount_type' => null,
                'discount_value' => null,
                'making_charge' => round($data['price'] * 0.08, 2),
                'gst_percentage' => 3.00,
                'final_price' => $data['price'],
                'stock_quantity' => $data['stock'],
                'minimum_stock' => 5,
                'is_active' => true,
                'is_featured' => $data['featured'],
                'is_new_arrival' => $i % 4 === 0,
                'is_best_seller' => $data['best'],
                'is_trending' => $i % 5 === 0,
                'is_on_sale' => $data['mrp'] > $data['price'],
                'meta_title' => $data['name'],
                'meta_description' => "Shop the {$data['name']} — certified {$data['metal']} jewellery from Aurelle.",
            ]);

            if ($data['sizes']) {
                foreach ($data['sizes'] as $size) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $sku.'-'.Str::slug($size),
                        'size' => $size,
                        'metal' => $data['metal'],
                        'purity' => $data['purity'],
                        'stock_quantity' => max((int) ($data['stock'] / count($data['sizes'])), 1),
                        'status' => 'active',
                    ]);
                }
            }

            $productModels[] = $product;
        }

        // Demo customers
        $customerNames = ['Ananya Rao', 'Karthik Menon', 'Priya Nair', 'Rohan Kapoor', 'Meera Iyer', 'Divya Krishnan'];
        $customers = [];
        foreach ($customerNames as $i => $name) {
            $customers[] = User::create([
                'name' => $name,
                'email' => Str::slug($name).'@example.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+91 9'.random_int(100000000, 999999999),
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now()->subDays(random_int(10, 400)),
            ]);
        }

        $admin = User::where('role', 'admin')->first();

        // Demo orders
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'delivered', 'cancelled'];
        $paymentMethods = ['UPI', 'Credit Card', 'Net Banking', 'COD'];

        foreach (range(1, 10) as $n) {
            $customer = $customers[array_rand($customers)];
            $status = $statuses[array_rand($statuses)];
            $items = array_rand(array_flip(array_map(fn ($p) => $p->id, $productModels)), min(2, count($productModels)));
            $items = is_array($items) ? $items : [$items];

            $subtotal = 0;
            $orderItemsData = [];
            foreach ($items as $productId) {
                $product = collect($productModels)->firstWhere('id', $productId);
                $qty = random_int(1, 2);
                $total = (float) $product->selling_price * $qty;
                $subtotal += $total;
                $orderItemsData[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'total' => $total,
                ];
            }

            $gst = round($subtotal * 0.03, 2);
            $grandTotal = $subtotal + $gst;
            $paymentStatus = $status === 'cancelled' ? 'refunded' : ($status === 'pending' ? 'pending' : 'paid');

            $order = Order::create([
                'order_number' => 'ORD-'.now()->format('Y').'-'.str_pad((string) (10000 + $n), 5, '0', STR_PAD_LEFT),
                'user_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'transaction_id' => $paymentStatus === 'paid' ? 'TXN'.strtoupper(Str::random(10)) : null,
                'payment_gateway' => $paymentStatus === 'paid' ? 'Razorpay' : null,
                'paid_at' => $paymentStatus === 'paid' ? now()->subDays(random_int(1, 30)) : null,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'shipping_charge' => 0,
                'gst_amount' => $gst,
                'grand_total' => $grandTotal,
                'paid_amount' => $paymentStatus === 'paid' ? $grandTotal : 0,
                'shipping_address' => [
                    'name' => $customer->name,
                    'line1' => '14, Lavender Residency',
                    'line2' => 'Indiranagar 100 Feet Road',
                    'city' => 'Bengaluru',
                    'state' => 'Karnataka',
                    'pincode' => '560038',
                    'country' => 'India',
                    'phone' => $customer->phone,
                ],
                'courier_name' => in_array($status, ['shipped', 'delivered']) ? 'BlueDart' : null,
                'tracking_number' => in_array($status, ['shipped', 'delivered']) ? strtoupper(Str::random(12)) : null,
                'estimated_delivery' => now()->addDays(random_int(2, 7)),
                'delivered_at' => $status === 'delivered' ? now()->subDays(random_int(1, 15)) : null,
                'created_at' => now()->subDays(random_int(1, 60)),
            ]);

            foreach ($orderItemsData as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'sku' => $item['product']->sku,
                    'metal' => $item['product']->metal_type,
                    'purity' => $item['product']->purity,
                    'quantity' => $item['qty'],
                    'price' => $item['product']->selling_price,
                    'total' => $item['total'],
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'remark' => 'Order placed by customer.',
                'updated_by' => null,
                'created_at' => $order->created_at,
            ]);

            if ($status !== 'pending') {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $status,
                    'remark' => "Order marked as {$status}.",
                    'updated_by' => $admin?->id,
                    'created_at' => $order->created_at->addHours(random_int(2, 48)),
                ]);
            }

            // Reviews for delivered orders
            if ($status === 'delivered' && random_int(0, 1)) {
                $item = $orderItemsData[0];
                Review::create([
                    'customer_id' => $customer->id,
                    'product_id' => $item['product']->id,
                    'order_id' => $order->id,
                    'rating' => random_int(3, 5),
                    'title' => 'Beautiful craftsmanship',
                    'review' => 'The piece exceeded my expectations — the finish is impeccable and it photographs even better in person.',
                    'status' => random_int(0, 1) ? 'approved' : 'pending',
                    'approved_by' => random_int(0, 1) ? $admin?->id : null,
                    'approved_at' => random_int(0, 1) ? now() : null,
                ]);
            }
        }

        // Feedback
        $feedbackTypes = ['general', 'website', 'product', 'delivery', 'payment', 'complaint', 'suggestion'];
        foreach (range(1, 6) as $n) {
            $customer = $customers[array_rand($customers)];
            Feedback::create([
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'type' => $feedbackTypes[array_rand($feedbackTypes)],
                'subject' => 'Feedback regarding recent experience',
                'message' => 'I wanted to share some thoughts about my recent shopping experience with Aurelle.',
                'status' => ['new', 'in_progress', 'resolved', 'closed'][array_rand(['new', 'in_progress', 'resolved', 'closed'])],
                'created_at' => now()->subDays(random_int(1, 40)),
            ]);
        }

        // Contact messages
        foreach (range(1, 6) as $n) {
            ContactMessage::create([
                'ticket_no' => ContactMessage::generateTicketNumber(),
                'name' => $customerNames[array_rand($customerNames)],
                'email' => 'guest'.$n.'@example.com',
                'phone' => '+91 9'.random_int(100000000, 999999999),
                'subject' => 'Question about an order',
                'message' => 'Could you please help me with a query about my recent purchase?',
                'priority' => ['low', 'normal', 'high', 'urgent'][array_rand(['low', 'normal', 'high', 'urgent'])],
                'status' => ['new', 'open', 'in_progress', 'resolved'][array_rand(['new', 'open', 'in_progress', 'resolved'])],
                'created_at' => now()->subDays(random_int(1, 30)),
            ]);
        }

        // FAQs
        $faqs = [
            ['q' => 'Is your jewellery certified?', 'a' => 'Yes, all our diamond and gold jewellery comes with certification from recognised authorities such as IGI and BIS hallmarking.', 'cat' => 'products'],
            ['q' => 'What is your return policy?', 'a' => 'We offer a 15-day easy return window from the date of delivery.', 'cat' => 'returns'],
            ['q' => 'Do you offer free shipping?', 'a' => 'Yes, on all orders above ₹2,999.', 'cat' => 'shipping'],
            ['q' => 'What payment methods do you accept?', 'a' => 'UPI, credit/debit cards, net banking, wallets, and COD on eligible orders.', 'cat' => 'payments'],
            ['q' => 'How do I care for my jewellery?', 'a' => 'Store separately in a soft pouch, avoid contact with perfume and moisture, and clean gently with a soft cloth.', 'cat' => 'jewellery_care'],
        ];
        foreach ($faqs as $i => $faq) {
            Faq::create([
                'question' => $faq['q'],
                'answer' => $faq['a'],
                'category' => $faq['cat'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        // Coupons
        Coupon::create([
            'code' => 'WELCOME10',
            'description' => '10% off on your first order',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'minimum_order' => 5000,
            'maximum_discount' => 5000,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(3),
            'usage_limit' => 500,
            'usage_per_customer' => 1,
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'FLAT2000',
            'description' => 'Flat ₹2000 off on orders above ₹50,000',
            'discount_type' => 'fixed',
            'discount_value' => 2000,
            'minimum_order' => 50000,
            'maximum_discount' => null,
            'start_date' => now()->subWeek(),
            'end_date' => now()->addMonth(),
            'usage_limit' => 200,
            'usage_per_customer' => 1,
            'is_active' => true,
        ]);
    }

    protected function categoryFor(string $name): Category
    {
        $category = Category::query()->where('slug', Str::slug($name))->first();

        if (! $category) {
            throw new \RuntimeException("Missing category master data for [{$name}]. Run migrations before seeding the catalog.");
        }

        return $category;
    }
}

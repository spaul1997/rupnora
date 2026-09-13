<?php

namespace App\Http\Controllers;

use App\Support\Catalog;
use App\Support\StorefrontCatalog;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
    protected function customer(): array
    {
        return [
            'name' => 'Ananya Rao',
            'first_name' => 'Ananya',
            'last_name' => 'Rao',
            'email' => 'ananya.rao@example.com',
            'phone' => '+91 98765 43210',
            'joined' => 'March 2024',
            'reward_points' => 1240,
        ];
    }

    public function dashboard()
    {
        $orders = Catalog::orders();

        return view('account.dashboard', [
            'title' => 'My Account',
            'customer' => $this->customer(),
            'orders' => array_slice($orders, 0, 3),
            'totalOrders' => count($orders),
            'activeOrders' => collect($orders)->whereIn('status', ['Processing', 'Confirmed', 'Shipped', 'Out for Delivery'])->count(),
            'wishlistCount' => 2,
            'address' => Catalog::addresses()[0],
            'recommended' => StorefrontCatalog::bestSellers(4),
        ]);
    }

    public function orders()
    {
        return view('account.orders', [
            'title' => 'My Orders',
            'customer' => $this->customer(),
            'orders' => Catalog::orders(),
        ]);
    }

    public function orderShow(string $id)
    {
        $order = Catalog::order($id);

        abort_if(! $order, Response::HTTP_NOT_FOUND);

        $subtotal = collect($order['items'])->sum(fn ($item) => $item['product']['price'] * $item['qty']);

        return view('account.order-details', [
            'title' => 'Order ' . $order['id'],
            'customer' => $this->customer(),
            'order' => $order,
            'subtotal' => $subtotal,
            'shipping' => 0,
            'tax' => round($subtotal * 0.03),
        ]);
    }

    public function wishlist()
    {
        $ids = ['eternal-bloom-diamond-ring', 'royal-heritage-gold-necklace', 'aurora-diamond-studs'];

        return view('account.wishlist', [
            'title' => 'Wishlist',
            'customer' => $this->customer(),
            'products' => collect($ids)->map(fn ($id) => StorefrontCatalog::product($id))->filter()->values()->all(),
        ]);
    }

    public function addresses()
    {
        return view('account.addresses', [
            'title' => 'Saved Addresses',
            'customer' => $this->customer(),
            'addresses' => Catalog::addresses(),
        ]);
    }

    public function profile()
    {
        return view('account.profile', [
            'title' => 'Profile',
            'customer' => $this->customer(),
        ]);
    }

    public function changePassword()
    {
        return view('account.change-password', [
            'title' => 'Change Password',
            'customer' => $this->customer(),
        ]);
    }

    public function notifications()
    {
        $notifications = [
            ['title' => 'Your order has been shipped', 'body' => 'ORD-2026-10399 is on its way and should arrive by 26 Aug 2026.', 'time' => '2 days ago', 'unread' => true],
            ['title' => 'Price drop on your wishlist item', 'body' => 'Aurora Diamond Stud Earrings is now 11% off.', 'time' => '4 days ago', 'unread' => true],
            ['title' => 'Order delivered', 'body' => 'ORD-2026-10482 was delivered successfully. We hope you love it!', 'time' => '1 week ago', 'unread' => false],
            ['title' => 'Welcome to Aurelle Rewards', 'body' => 'You have earned 240 reward points on your last purchase.', 'time' => '2 weeks ago', 'unread' => false],
        ];

        return view('account.notifications', [
            'title' => 'Notifications',
            'customer' => $this->customer(),
            'notifications' => $notifications,
        ]);
    }

    public function support()
    {
        return view('account.support', [
            'title' => 'Support',
            'customer' => $this->customer(),
            'faqs' => Catalog::faqs(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\WebsiteSetting;
use App\Support\Catalog;
use App\Support\CheckoutOrders;
use App\Support\ShoppingCart;
use App\Support\StorefrontCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
    protected function customer(): array
    {
        $customer = Auth::user();
        $name = $customer->name;
        $nameParts = preg_split('/\s+/', trim($name), 2);

        return [
            'name' => $name,
            'first_name' => $nameParts[0] ?? '',
            'last_name' => $nameParts[1] ?? '',
            'email' => $customer->email,
            'phone' => $customer->phone,
            'joined' => $customer->created_at->format('F Y'),
            'reward_points' => 1240,
        ];
    }

    public function dashboard()
    {
        $orders = CheckoutOrders::all();

        return view('account.dashboard', [
            'title' => 'My Account',
            'customer' => $this->customer(),
            'orders' => array_slice($orders, 0, 3),
            'totalOrders' => count($orders),
            'activeOrders' => collect($orders)->whereIn('status', ['Processing', 'Confirmed', 'Shipped', 'Out for Delivery'])->count(),
            'wishlistCount' => count(ShoppingCart::wishlistIds()),
            'address' => Catalog::addresses()[0],
            'recommended' => StorefrontCatalog::bestSellers(4),
        ]);
    }

    public function orders()
    {
        return view('account.orders', [
            'title' => 'My Orders',
            'customer' => $this->customer(),
            'orders' => CheckoutOrders::all(),
        ]);
    }

    public function orderShow(string $id)
    {
        $order = CheckoutOrders::find($id);

        abort_if(! $order, Response::HTTP_NOT_FOUND);

        $subtotal = collect($order['items'])->sum(fn ($item) => $item['product']['price'] * $item['qty']);

        return view('account.order-details', [
            'title' => 'Order '.$order['id'],
            'customer' => $this->customer(),
            'order' => $order,
            'subtotal' => $subtotal,
            'shipping' => 0,
            'tax' => round($subtotal * 0.03),
        ]);
    }

    public function wishlist()
    {
        $ids = ShoppingCart::wishlistIds();

        return view('account.wishlist', [
            'title' => 'Wishlist',
            'customer' => $this->customer(),
            'products' => collect($ids)->map(fn ($id) => StorefrontCatalog::product($id))->filter()->values()->all(),
        ]);
    }

    public function removeWishlist(string $product): JsonResponse
    {
        ShoppingCart::removeWishlistId($product);
        $wishlistIds = ShoppingCart::wishlistIds();

        return response()->json([
            'count' => count($wishlistIds),
            'wishlistIds' => $wishlistIds,
            'message' => 'Removed from wishlist',
        ]);
    }

    public function addWishlist(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required'],
        ]);

        abort_if(! ShoppingCart::addWishlistId($data['product_id']), Response::HTTP_NOT_FOUND);

        $wishlistIds = ShoppingCart::wishlistIds();

        return response()->json([
            'count' => count($wishlistIds),
            'wishlistIds' => $wishlistIds,
            'message' => 'Added to wishlist',
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
            'settings' => WebsiteSetting::current(),
        ]);
    }
}

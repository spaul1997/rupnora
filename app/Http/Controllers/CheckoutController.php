<?php

namespace App\Http\Controllers;

use App\Support\Catalog;
use App\Support\CheckoutAddresses;
use App\Support\CheckoutOrders;
use App\Support\ShoppingCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('pages.checkout', [
            'title' => 'Checkout',
            'items' => ShoppingCart::items(),
            'addresses' => CheckoutAddresses::all(),
        ]);
    }

    public function storeAddress(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:Home,Office,Other'],
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'line1' => ['required', 'string', 'max:180'],
            'line2' => ['nullable', 'string', 'max:180'],
            'landmark' => ['nullable', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'regex:/^[1-9][0-9]{5}$/'],
            'country' => ['nullable', 'string', 'max:80'],
        ]);

        return response()->json([
            'address' => CheckoutAddresses::add($data),
            'addresses' => CheckoutAddresses::all(),
            'message' => 'Address added',
        ]);
    }

    public function placeOrder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address_id' => ['required'],
            'delivery' => ['required', 'in:standard,express'],
            'payment' => ['required', 'in:upi,card,netbanking,wallet,cod'],
        ]);

        if (count(ShoppingCart::items()) === 0) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        $hasAddress = collect(CheckoutAddresses::all())
            ->contains(fn (array $address) => (string) $address['id'] === (string) $data['address_id']);

        if (! $hasAddress) {
            throw ValidationException::withMessages([
                'address_id' => 'Please select or add a delivery address.',
            ]);
        }

        $order = CheckoutOrders::create($data);
        ShoppingCart::clear();

        return response()->json([
            'orderId' => $order['id'],
            'redirect' => route('order.success', $order['id']),
            'cartCount' => 0,
            'message' => 'Order placed successfully',
        ]);
    }

    public function success(?string $id = null)
    {
        $order = $id ? CheckoutOrders::find($id) : CheckoutOrders::latest();

        if (! $order) {
            return redirect()->route('cart');
        }

        return view('pages.order-success', [
            'title' => 'Order Confirmed',
            'order' => $order,
        ]);
    }
}

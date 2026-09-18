<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorefrontAddressRequest;
use App\Mail\OrderSuccessMail;
use App\Models\Order;
use App\Models\WebsiteSetting;
use App\Support\CheckoutAddresses;
use App\Support\CheckoutOrders;
use App\Support\ShoppingCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index()
    {
        $settings = WebsiteSetting::current();

        return view('pages.checkout', [
            'title' => 'Checkout',
            'items' => ShoppingCart::items(),
            'addresses' => CheckoutAddresses::all(),
            'expressDeliveryCharge' => (float) $settings->express_delivery_charge,
            'codOrderLimit' => (float) $settings->cod_order_limit,
        ]);
    }

    public function storeAddress(StorefrontAddressRequest $request): JsonResponse
    {
        $data = $request->validated();

        return response()->json([
            'address' => CheckoutAddresses::add($data),
            'addresses' => CheckoutAddresses::all(),
            'message' => 'Address added',
        ]);
    }

    public function updateAddress(StorefrontAddressRequest $request, string $address): JsonResponse
    {
        return response()->json([
            'address' => CheckoutAddresses::update($address, $request->validated()),
            'addresses' => CheckoutAddresses::all(),
            'message' => 'Address updated',
        ]);
    }

    public function placeOrder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address_id' => ['required'],
            'delivery' => ['required', 'in:standard,express'],
            'payment' => ['required', 'in:online,cod'],
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

        if ($data['payment'] === 'cod') {
            $settings = WebsiteSetting::current();
            $shipping = $data['delivery'] === 'express' ? (float) $settings->express_delivery_charge : 0;
            $orderTotal = round(ShoppingCart::summary()['total'] + $shipping, 2);
            $limit = (float) $settings->cod_order_limit;

            if ($orderTotal >= $limit) {
                $message = $limit > 0
                    ? 'Cash on Delivery is available only for orders below ₹'.number_format($limit, 2).'.'
                    : 'Cash on Delivery is currently unavailable.';

                throw ValidationException::withMessages([
                    'payment' => $message.' Please choose Online Payment.',
                ]);
            }
        }

        $order = CheckoutOrders::create($data);
        ShoppingCart::clear();
        $this->sendOrderConfirmation($order['id']);

        return response()->json([
            'orderId' => $order['id'],
            'redirect' => route('order.success', $order['id']),
            'cartCount' => 0,
            'message' => 'Order placed successfully',
        ]);
    }

    private function sendOrderConfirmation(string $orderNumber): void
    {
        try {
            $order = Order::query()->where('order_number', $orderNumber)->firstOrFail();
            $pendingMail = Mail::to($order->customer_email);
            $supportEmail = WebsiteSetting::current()->support_email;

            if ($supportEmail && strcasecmp($supportEmail, $order->customer_email) !== 0) {
                $pendingMail->bcc($supportEmail);
            }

            $pendingMail->sendNow(new OrderSuccessMail($order));
        } catch (\Throwable $exception) {
            report($exception);
        }
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

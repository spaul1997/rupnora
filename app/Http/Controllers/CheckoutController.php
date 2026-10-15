<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorefrontAddressRequest;
use App\Mail\OrderSuccessMail;
use App\Models\Order;
use App\Models\WebsiteSetting;
use App\Services\CheckoutShippingService;
use App\Support\CheckoutAddresses;
use App\Support\CheckoutOrders;
use App\Support\ShoppingCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
    public function index()
    {
        $settings = WebsiteSetting::current();

        return view('pages.checkout', [
            'title' => 'Checkout',
            'items' => ShoppingCart::items(),
            'cartSummary' => ShoppingCart::summary(),
            'addresses' => CheckoutAddresses::all(),
            'expressDeliveryCharge' => (float) $settings->express_delivery_charge,
            'freeShippingThreshold' => (float) $settings->free_shipping_threshold,
            'codOrderLimit' => (float) $settings->cod_order_limit,
            'giftWrapCharge' => (float) config('checkout.gift_wrap.charge', 50),
            'giftMessageLimit' => (int) config('checkout.gift_wrap.message_limit', 248),
            'giftMessageTemplates' => config('checkout.gift_wrap.templates', []),
        ]);
    }

    public function shippingQuote(Request $request, CheckoutShippingService $shipping): JsonResponse
    {
        $data = $request->validate([
            'address_id' => ['required'],
            'payment' => ['nullable', 'in:online,cod'],
        ]);
        $items = ShoppingCart::items();

        if ($items === []) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        $address = $this->selectedAddress($data['address_id']);

        if (! $address) {
            throw ValidationException::withMessages(['address_id' => 'Please select or add a delivery address.']);
        }

        try {
            $quote = $shipping->quote(
                $items,
                $address,
                (float) ShoppingCart::summary()['total'],
                (string) ($data['payment'] ?? 'online'),
            );
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Shipping rates are temporarily unavailable. Please try again shortly.',
            ], Response::HTTP_BAD_GATEWAY);
        }

        if (! $quote['available']) {
            return response()->json([
                ...$quote,
                'errors' => ['shipping' => [$quote['message']]],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json($quote);
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

    public function placeOrder(Request $request, CheckoutShippingService $shipping): JsonResponse
    {
        $data = $request->validate([
            'address_id' => ['required'],
            'delivery' => ['required', 'in:standard,express'],
            'payment' => ['required', 'in:online,cod'],
            'gift_wrap' => ['sometimes', 'boolean'],
            'gift_message_category' => [
                Rule::excludeIf(fn () => ! $request->boolean('gift_wrap')),
                'nullable',
                'string',
                Rule::in(array_keys(config('checkout.gift_wrap.templates', []))),
            ],
            'gift_message' => [
                Rule::excludeIf(fn () => ! $request->boolean('gift_wrap')),
                'nullable',
                'string',
                'max:'.config('checkout.gift_wrap.message_limit', 248),
            ],
            'gift_to' => [Rule::excludeIf(fn () => ! $request->boolean('gift_wrap')), 'nullable', 'string', 'max:100'],
            'gift_from' => [Rule::excludeIf(fn () => ! $request->boolean('gift_wrap')), 'nullable', 'string', 'max:100'],
        ]);

        $data['gift_wrap'] = (bool) ($data['gift_wrap'] ?? false);

        if (count(ShoppingCart::items()) === 0) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        $address = $this->selectedAddress($data['address_id']);

        if (! $address) {
            throw ValidationException::withMessages([
                'address_id' => 'Please select or add a delivery address.',
            ]);
        }

        try {
            $shippingQuote = $shipping->quote(
                ShoppingCart::items(),
                $address,
                (float) ShoppingCart::summary()['total'],
                $data['payment'],
            );
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'shipping' => 'Shipping rates are temporarily unavailable. Please try again shortly.',
            ]);
        }

        if (! $shippingQuote['available']) {
            throw ValidationException::withMessages(['shipping' => $shippingQuote['message']]);
        }

        $settings = WebsiteSetting::current();
        $expressSurcharge = $data['delivery'] === 'express' ? (float) $settings->express_delivery_charge : 0;
        $data['shipping_charge'] = round((float) $shippingQuote['shipping_charge'] + $expressSurcharge, 2);
        $data['shipping_quote'] = $shippingQuote;

        if ($data['payment'] === 'cod') {
            $giftWrapCharge = $data['gift_wrap'] ? (float) config('checkout.gift_wrap.charge', 50) : 0;
            $orderTotal = round(ShoppingCart::summary()['total'] + $data['shipping_charge'] + $giftWrapCharge, 2);
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
            $marketingEmail = trim((string) config('marketing.cc_email'));

            if ($marketingEmail !== '' && strcasecmp($marketingEmail, $order->customer_email) !== 0) {
                $pendingMail->bcc($marketingEmail);
            }

            $pendingMail->sendNow(new OrderSuccessMail($order));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function selectedAddress(mixed $addressId): ?array
    {
        return collect(CheckoutAddresses::all())
            ->first(fn (array $address): bool => (string) $address['id'] === (string) $addressId);
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateShiprocketAwbRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Requests\Admin\UpdateShiprocketAddressRequest;
use App\Mail\TrackingGeneratedMail;
use App\Models\Order;
use App\Models\Product;
use App\Services\AffiliateCommissionService;
use App\Services\ShiprocketService;
use App\Services\ShiprocketTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with('user')
            ->withCount('items')
            ->when($request->order_number, fn ($q, $v) => $q->where('order_number', 'like', "%{$v}%"))
            ->when($request->customer, function ($q, $v) {
                $q->where(function ($q) use ($v) {
                    $q->where('customer_name', 'like', "%{$v}%")->orWhere('customer_email', 'like', "%{$v}%");
                });
            })
            ->when($request->mobile, fn ($q, $v) => $q->where('customer_phone', 'like', "%{$v}%"))
            ->when($request->date_from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->payment_status, fn ($q, $v) => $q->where('payment_status', $v))
            ->when($request->payment_method, fn ($q, $v) => $q->where('payment_method', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['items.product', 'statusHistories.updatedBy', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'courier_name' => ['nullable', 'string', 'max:255'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'tracking_url' => ['nullable', 'string', 'max:500'],
            'estimated_delivery' => ['nullable', 'date'],
        ]);

        $order->update($data);

        return back()->with('success', 'Shipping details updated successfully.');
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        DB::transaction(function () use ($request, $order) {
            $order->update(['status' => $request->status]);

            $order->statusHistories()->create([
                'status' => $request->status,
                'remark' => $request->remark,
                'updated_by' => auth()->id(),
            ]);

            if ($request->status === 'delivered' && ! $order->delivered_at) {
                $order->update(['delivered_at' => now()]);
            }

            if ($request->status === 'delivered') {
                app(AffiliateCommissionService::class)->markDelivered($order->refresh());
            }
            if (in_array($request->status, ['cancelled', 'returned', 'refunded'], true)) {
                app(AffiliateCommissionService::class)->reconcileReversal($order->refresh());
            }
        });

        return back()->with('success', 'Order status updated successfully.');
    }

    public function updatePaymentStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'payment_status' => ['required', 'in:pending,paid,failed,refunded,partial_refund,chargeback,cod'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($order, $data) {
            $update = ['payment_status' => $data['payment_status']];

            if ($data['payment_status'] === 'paid' && ! $order->paid_at) {
                $update['paid_at'] = now();
                $update['paid_amount'] = $data['paid_amount'] ?? $order->grand_total;
            }
            if (isset($data['refund_amount'])) {
                $update['refund_amount'] = $data['refund_amount'];
            }

            $order->update($update);

            $order->statusHistories()->create([
                'status' => 'payment_'.$data['payment_status'],
                'remark' => 'Payment status updated to '.$data['payment_status'].'.',
                'updated_by' => auth()->id(),
            ]);

            if ($data['payment_status'] === 'paid') {
                app(AffiliateCommissionService::class)->createForPaidOrder($order->refresh());
            }
            if (in_array($data['payment_status'], ['refunded', 'partial_refund', 'chargeback'], true)) {
                app(AffiliateCommissionService::class)->reconcileReversal($order->refresh());
            }
        });

        return back()->with('success', 'Payment status updated successfully.');
    }

    public function updateReturnedItems(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'returned_quantities' => ['required', 'array'],
            'returned_quantities.*' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($order, $data) {
            $order = Order::query()->with('items')->lockForUpdate()->findOrFail($order->id);
            $ratios = [];
            foreach ($order->items as $item) {
                $quantity = (int) ($data['returned_quantities'][$item->id] ?? $item->returned_quantity);
                if ($quantity > $item->quantity) {
                    throw ValidationException::withMessages([
                        "returned_quantities.{$item->id}" => 'Returned quantity cannot exceed the ordered quantity.',
                    ]);
                }
                if ($quantity < $item->returned_quantity) {
                    throw ValidationException::withMessages([
                        "returned_quantities.{$item->id}" => 'Returned quantity cannot be reduced after reconciliation.',
                    ]);
                }
                $item->update(['returned_quantity' => $quantity]);
                $ratios[$item->id] = $item->quantity > 0 ? $quantity / $item->quantity : 0;
            }
            app(AffiliateCommissionService::class)->reconcileReversal($order->refresh(), $ratios);
            $order->statusHistories()->create([
                'status' => 'partial_return_reconciled',
                'remark' => 'Returned item quantities updated and affiliate commission reconciled.',
                'updated_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Returned quantities and affiliate commissions reconciled.');
    }

    public function createShiprocketOrder(Order $order, ShiprocketService $shiprocket): RedirectResponse
    {
        if ($order->shiprocket_order_id) {
            return back()->with('success', 'This order is already available in Shiprocket.');
        }

        try {
            $response = $shiprocket->createOrder($order);

            $order->update([
                'shiprocket_order_id' => (string) $response['order_id'],
                'shiprocket_shipment_id' => (string) $response['shipment_id'],
                'shiprocket_status' => (string) ($response['status'] ?? 'NEW'),
                'shiprocket_synced_at' => now(),
                'tracking_number' => filled($response['awb_code'] ?? null)
                    ? (string) $response['awb_code']
                    : $order->tracking_number,
                'courier_name' => filled($response['courier_name'] ?? null)
                    ? (string) $response['courier_name']
                    : $order->courier_name,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['shiprocket' => $exception->getMessage()]);
        }

        return back()->with('success', 'Order sent to Shiprocket successfully.');
    }

    public function listShiprocketCouriers(Order $order, ShiprocketService $shiprocket): RedirectResponse
    {
        if (! $order->shiprocket_order_id || ! $order->shiprocket_shipment_id) {
            return back()->withErrors([
                'shiprocket_awb' => 'Create the Shiprocket order before generating an AWB.',
            ]);
        }

        if ($order->tracking_number) {
            return back()->with('success', 'Tracking has already been generated for this order.');
        }

        if ($order->status !== 'shipped') {
            return back()->withErrors([
                'shiprocket_awb' => 'Update the order status to Shipped before generating an AWB.',
            ]);
        }

        try {
            $couriers = $shiprocket->serviceableCouriers($order);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['shiprocket_awb' => $exception->getMessage()]);
        }

        if ($couriers === []) {
            return back()->withErrors([
                'shiprocket_awb' => 'No Shiprocket courier is currently serviceable for this order.',
            ]);
        }

        return back()->with('shiprocket_couriers', $couriers);
    }

    public function generateShiprocketAwb(
        GenerateShiprocketAwbRequest $request,
        Order $order,
        ShiprocketService $shiprocket,
    ): RedirectResponse {
        if (! $order->shiprocket_order_id || ! $order->shiprocket_shipment_id) {
            return back()->withErrors([
                'shiprocket_awb' => 'Create the Shiprocket order before generating an AWB.',
            ]);
        }

        if ($order->tracking_number) {
            return back()->with('success', 'Tracking has already been generated for this order.');
        }

        if ($order->status !== 'shipped') {
            return back()->withErrors([
                'shiprocket_awb' => 'Update the order status to Shipped before generating an AWB.',
            ]);
        }

        try {
            $awb = $shiprocket->generateAwb($order, (int) $request->validated('courier_id'));
            $trackingUrl = $shiprocket->trackingUrl($awb['awb_code']);
            $trackingWasCreated = false;
            $mailOrder = null;

            DB::transaction(function () use ($order, $awb, $trackingUrl, &$trackingWasCreated, &$mailOrder): void {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

                if ($lockedOrder->tracking_number) {
                    return;
                }

                $lockedOrder->update([
                    'shiprocket_shipment_id' => $awb['shipment_id'],
                    'shiprocket_status' => 'AWB ASSIGNED',
                    'shiprocket_synced_at' => now(),
                    'courier_name' => $awb['courier_name'],
                    'tracking_number' => $awb['awb_code'],
                    'tracking_url' => $trackingUrl,
                ]);
                $lockedOrder->statusHistories()->create([
                    'status' => 'awb_assigned',
                    'remark' => 'Shiprocket AWB '.$awb['awb_code'].' assigned to '.$awb['courier_name'].'.',
                    'updated_by' => auth()->id(),
                ]);

                $trackingWasCreated = true;
                $mailOrder = $lockedOrder->fresh();
            });

            if (! $trackingWasCreated || ! $mailOrder) {
                return back()->with('success', 'Tracking has already been generated for this order.');
            }

            Mail::to($mailOrder->customer_email)
                ->queue(new TrackingGeneratedMail($mailOrder));
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['shiprocket_awb' => $exception->getMessage()]);
        }

        return back()->with('success', 'AWB generated and the tracking email was queued successfully.');
    }

    public function refreshShiprocketTracking(
        Order $order,
        ShiprocketTrackingService $trackingService,
    ): RedirectResponse {
        if (! $order->tracking_number) {
            return back()->withErrors([
                'shiprocket_tracking' => 'Generate an AWB before refreshing Shiprocket tracking.',
            ]);
        }

        try {
            $tracking = $trackingService->refresh($order, auth()->id());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['shiprocket_tracking' => $exception->getMessage()]);
        }

        return back()->with('success', 'Tracking refreshed successfully: '.$tracking['status'].'.');
    }

    public function updateShiprocketAddress(
        UpdateShiprocketAddressRequest $request,
        Order $order,
        ShiprocketService $shiprocket,
    ): RedirectResponse {
        if (! $order->shiprocket_order_id) {
            return back()->withErrors([
                'shiprocket_address' => 'Create the Shiprocket order before changing its delivery address.',
            ]);
        }

        $data = $request->validated();

        try {
            $shiprocket->updateDeliveryAddress($order, $data);

            $order->update([
                'shipping_address' => [
                    ...($order->shipping_address ?? []),
                    'name' => $data['shipping_customer_name'],
                    'phone' => $data['shipping_phone'],
                    'line1' => $data['shipping_address'],
                    'line2' => $data['shipping_address_2'] ?? '',
                    'landmark' => '',
                    'city' => $data['shipping_city'],
                    'district' => '',
                    'state' => $data['shipping_state'],
                    'country' => $data['shipping_country'],
                    'pincode' => $data['shipping_pincode'],
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['shiprocket_address' => $exception->getMessage()])->withInput();
        }

        return back()->with('success', 'Delivery address updated in Shiprocket successfully.');
    }

    public function cancelShiprocketOrder(Order $order, ShiprocketService $shiprocket): RedirectResponse
    {
        if (! $order->shiprocket_order_id) {
            return back()->withErrors([
                'shiprocket_cancel' => 'Create the Shiprocket order before cancelling it.',
            ]);
        }

        if ($order->status === 'cancelled') {
            return back()->with('success', 'This order is already cancelled.');
        }

        try {
            $shiprocket->cancelOrder($order);

            DB::transaction(function () use ($order) {
                $order = Order::query()->with('items')->lockForUpdate()->findOrFail($order->id);

                if ($order->stock_reserved) {
                    $quantities = $order->items->groupBy('product_id')
                        ->map(fn ($items) => $items->sum('quantity'));
                    $products = Product::query()->whereIn('id', $quantities->keys())
                        ->orderBy('id')->lockForUpdate()->get();

                    foreach ($products as $product) {
                        $product->stock_quantity += $quantities[$product->id];
                        $product->save();
                    }
                }

                $order->update([
                    'status' => 'cancelled',
                    'shiprocket_status' => 'CANCELLED',
                    'stock_reserved' => false,
                ]);
                $order->statusHistories()->create([
                    'status' => 'cancelled',
                    'remark' => 'Order cancelled in Shiprocket by admin.',
                    'updated_by' => auth()->id(),
                ]);
                app(AffiliateCommissionService::class)->reconcileReversal($order->refresh());
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['shiprocket_cancel' => $exception->getMessage()]);
        }

        return back()->with('success', 'Order cancelled in Shiprocket successfully.');
    }

    public function invoice(Order $order): View
    {
        $order->load(['items', 'user']);

        return view('admin.orders.invoice', compact('order'));
    }

    public function export(): StreamedResponse
    {
        $filename = 'orders-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order Number', 'Date', 'Customer', 'Status', 'Payment Status', 'Grand Total']);

            Order::orderByDesc('created_at')->chunk(200, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->order_number,
                        $order->created_at->format('Y-m-d'),
                        $order->customer_name,
                        $order->status,
                        $order->payment_status,
                        $order->grand_total,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        });

        return back()->with('success', 'Order status updated successfully.');
    }

    public function updatePaymentStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'payment_status' => ['required', 'in:pending,paid,failed,refunded,partial_refund,cod'],
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
        });

        return back()->with('success', 'Payment status updated successfully.');
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

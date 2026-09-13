<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public const PERIODS = ['today', 'yesterday', 'last_7_days', 'last_30_days', 'this_month', 'last_month', 'custom'];

    public function index(): View
    {
        return view('admin.reports.index');
    }

    public function sales(Request $request): View
    {
        [$from, $to] = $this->resolveRange($request);

        $orders = Order::where('payment_status', 'paid')->whereBetween('created_at', [$from, $to]);

        $summary = [
            'total_sales' => (float) (clone $orders)->sum('grand_total'),
            'order_count' => (clone $orders)->count(),
            'avg_order_value' => (float) (clone $orders)->avg('grand_total'),
        ];

        $dailySales = (clone $orders)
            ->selectRaw('DATE(created_at) as day, SUM(grand_total) as total, COUNT(*) as orders')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return view('admin.reports.sales', compact('summary', 'dailySales', 'from', 'to'));
    }

    public function orders(Request $request): View
    {
        [$from, $to] = $this->resolveRange($request);

        $orders = Order::whereBetween('created_at', [$from, $to])->latest()->paginate(25)->withQueryString();
        $statusCounts = Order::whereBetween('created_at', [$from, $to])->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        return view('admin.reports.orders', compact('orders', 'statusCounts', 'from', 'to'));
    }

    public function products(Request $request): View
    {
        [$from, $to] = $this->resolveRange($request);

        $products = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->select('products.id', 'products.name', 'products.sku', DB::raw('SUM(order_items.quantity) as units_sold'), DB::raw('SUM(order_items.total) as revenue'))
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('revenue')
            ->paginate(25)
            ->withQueryString();

        return view('admin.reports.products', compact('products', 'from', 'to'));
    }

    public function categories(Request $request): View
    {
        [$from, $to] = $this->resolveRange($request);

        $categories = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.payment_status', 'paid')
            ->select('categories.id', 'categories.name', DB::raw('SUM(order_items.quantity) as units_sold'), DB::raw('SUM(order_items.total) as revenue'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        return view('admin.reports.categories', compact('categories', 'from', 'to'));
    }

    public function customers(Request $request): View
    {
        [$from, $to] = $this->resolveRange($request);

        $customers = User::customers()
            ->withCount(['orders' => fn ($q) => $q->whereBetween('created_at', [$from, $to])])
            ->withSum(['orders as period_spend' => fn ($q) => $q->where('payment_status', 'paid')->whereBetween('created_at', [$from, $to])], 'grand_total')
            ->having('orders_count', '>', 0)
            ->orderByDesc('period_spend')
            ->paginate(25)
            ->withQueryString();

        return view('admin.reports.customers', compact('customers', 'from', 'to'));
    }

    public function inventory(): View
    {
        $products = Product::with('category')->orderBy('name')->paginate(25);

        return view('admin.reports.inventory', compact('products'));
    }

    public function lowStock(): View
    {
        $products = Product::with('category')->lowStock()->orderBy('stock_quantity')->paginate(25);

        return view('admin.reports.low-stock', compact('products'));
    }

    public function payments(Request $request): View
    {
        [$from, $to] = $this->resolveRange($request);

        $orders = Order::whereBetween('created_at', [$from, $to])->latest()->paginate(25)->withQueryString();
        $byMethod = Order::whereBetween('created_at', [$from, $to])->select('payment_method', DB::raw('SUM(grand_total) as total'), DB::raw('count(*) as orders'))->groupBy('payment_method')->get();

        return view('admin.reports.payments', compact('orders', 'byMethod', 'from', 'to'));
    }

    public function refunds(Request $request): View
    {
        [$from, $to] = $this->resolveRange($request);

        $orders = Order::whereIn('payment_status', ['refunded', 'partial_refund'])
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $totalRefunded = (float) Order::whereIn('payment_status', ['refunded', 'partial_refund'])->whereBetween('created_at', [$from, $to])->sum('refund_amount');

        return view('admin.reports.refunds', compact('orders', 'totalRefunded', 'from', 'to'));
    }

    public function export(Request $request, string $type, string $format): StreamedResponse
    {
        [$from, $to] = $this->resolveRange($request);

        $rows = match ($type) {
            'sales' => Order::where('payment_status', 'paid')->whereBetween('created_at', [$from, $to])->get(['order_number', 'created_at', 'customer_name', 'grand_total']),
            'orders' => Order::whereBetween('created_at', [$from, $to])->get(['order_number', 'created_at', 'customer_name', 'status', 'payment_status', 'grand_total']),
            'low-stock' => Product::lowStock()->get(['sku', 'name', 'stock_quantity', 'minimum_stock']),
            'inventory' => Product::get(['sku', 'name', 'stock_quantity', 'minimum_stock', 'stock_status']),
            default => collect(),
        };

        $filename = "{$type}-report-".now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            if ($rows->isNotEmpty()) {
                fputcsv($handle, array_keys($rows->first()->getAttributes()));
                foreach ($rows as $row) {
                    fputcsv($handle, $row->getAttributes());
                }
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function resolveRange(Request $request): array
    {
        $period = $request->input('period', 'last_30_days');

        return match ($period) {
            'today' => [Carbon::today(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday(), Carbon::yesterday()->endOfDay()],
            'last_7_days' => [Carbon::today()->subDays(6), Carbon::today()->endOfDay()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonthNoOverflow()->startOfMonth(), Carbon::now()->subMonthNoOverflow()->endOfMonth()],
            'custom' => [
                $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : Carbon::today()->subDays(29),
                $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : Carbon::today()->endOfDay(),
            ],
            default => [Carbon::today()->subDays(29), Carbon::today()->endOfDay()],
        };
    }
}

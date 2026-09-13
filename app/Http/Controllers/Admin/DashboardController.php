<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'total_sales' => (float) Order::where('payment_status', 'paid')->sum('grand_total'),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'total_products' => Product::count(),
            'low_stock_products' => Product::lowStock()->count(),
            'total_customers' => User::customers()->count(),
            'pending_reviews' => Review::where('status', 'pending')->count(),
        ];

        $recentOrders = Order::with('user')
            ->latest()
            ->take(6)
            ->get();

        $topSellingProducts = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select('products.id', 'products.name', 'products.sku', DB::raw('SUM(order_items.quantity) as units_sold'), DB::raw('SUM(order_items.total) as revenue'))
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('units_sold')
            ->take(5)
            ->get();

        $lowStockProducts = Product::with('category')
            ->lowStock()
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();

        $recentReviews = Review::with(['customer', 'product'])
            ->latest()
            ->take(5)
            ->get();

        $recentContacts = ContactMessage::latest()
            ->take(5)
            ->get();

        $salesOverview = [
            'today' => (float) Order::where('payment_status', 'paid')->whereDate('created_at', today())->sum('grand_total'),
            'last_7_days' => (float) Order::where('payment_status', 'paid')->where('created_at', '>=', now()->subDays(7))->sum('grand_total'),
            'this_month' => (float) Order::where('payment_status', 'paid')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('grand_total'),
            'this_year' => (float) Order::where('payment_status', 'paid')->whereYear('created_at', now()->year)->sum('grand_total'),
        ];

        $chartData = $this->buildChartData();

        return view('admin.dashboard.index', compact(
            'stats', 'recentOrders', 'topSellingProducts', 'lowStockProducts',
            'recentReviews', 'recentContacts', 'salesOverview', 'chartData'
        ));
    }

    protected function buildChartData(): array
    {
        $days = collect(range(13, 0))->map(fn ($i) => Carbon::today()->subDays($i));

        $salesByDay = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', Carbon::today()->subDays(13))
            ->selectRaw('DATE(created_at) as day, SUM(grand_total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return [
            'labels' => $days->map(fn ($d) => $d->format('d M'))->all(),
            'values' => $days->map(fn ($d) => round((float) ($salesByDay[$d->toDateString()] ?? 0), 2))->all(),
        ];
    }
}

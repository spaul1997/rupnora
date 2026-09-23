<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class VisitorTrackingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'in:all,pages,products'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $baseQuery = $this->filteredQuery($filters);

        $summary = [
            'total' => (clone $baseQuery)->count(),
            'unique_visitors' => (clone $baseQuery)->distinct()->count('ip_address'),
            'page_visits' => (clone $baseQuery)->whereNull('product_id')->count(),
            'product_visits' => (clone $baseQuery)->whereNotNull('product_id')->count(),
        ];

        $pageStats = (clone $baseQuery)
            ->whereNull('product_id')
            ->select(['page_path', 'page_title', 'route_name'])
            ->selectRaw('COUNT(*) as visits_count')
            ->selectRaw('COUNT(DISTINCT ip_address) as unique_visitors_count')
            ->selectRaw('MAX(visited_at) as last_visited_at')
            ->groupBy('page_path', 'page_title', 'route_name')
            ->orderByDesc('visits_count')
            ->limit(10)
            ->get();

        $productStats = (clone $baseQuery)
            ->whereNotNull('product_id')
            ->select('product_id')
            ->selectRaw('COUNT(*) as visits_count')
            ->selectRaw('COUNT(DISTINCT ip_address) as unique_visitors_count')
            ->selectRaw('MAX(visited_at) as last_visited_at')
            ->groupBy('product_id')
            ->with('product:id,name,slug,sku')
            ->orderByDesc('visits_count')
            ->limit(10)
            ->get();

        $visits = (clone $baseQuery)
            ->with(['product:id,name,slug,sku', 'user:id,name,email'])
            ->latest('visited_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.visitor-tracking.index', compact('summary', 'pageStats', 'productStats', 'visits'));
    }

    private function filteredQuery(array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $type = $filters['type'] ?? 'all';

        return VisitorLog::query()
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('page_path', 'like', "%{$search}%")
                        ->orWhere('page_title', 'like', "%{$search}%")
                        ->orWhere('route_name', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('user_agent', 'like', "%{$search}%")
                        ->orWhereHas('product', function (Builder $query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                        });
                });
            })
            ->when($type === 'pages', fn (Builder $query) => $query->whereNull('product_id'))
            ->when($type === 'products', fn (Builder $query) => $query->whereNotNull('product_id'))
            ->when(
                filled($filters['date_from'] ?? null),
                fn (Builder $query) => $query->where('visited_at', '>=', Carbon::parse($filters['date_from'])->startOfDay())
            )
            ->when(
                filled($filters['date_to'] ?? null),
                fn (Builder $query) => $query->where('visited_at', '<=', Carbon::parse($filters['date_to'])->endOfDay())
            );
    }
}

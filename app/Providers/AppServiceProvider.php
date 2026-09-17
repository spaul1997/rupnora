<?php

namespace App\Providers;

use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Observers\ContactMessageObserver;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        ContactMessage::observe(ContactMessageObserver::class);

        View::composer('admin.layouts.app', function ($view) {
            $view->with('adminNotifications', $this->adminNotifications());
        });
    }

    protected function adminNotifications(): array
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            return [];
        }

        $notifications = [];

        $pendingOrders = Order::where('status', 'pending')->count();
        if ($pendingOrders > 0) {
            $notifications[] = [
                'label' => "{$pendingOrders} pending order".($pendingOrders > 1 ? 's' : '').' need confirmation',
                'url' => route('admin.orders.index', ['status' => 'pending']),
                'icon' => 'order',
            ];
        }

        $pendingReviews = Review::where('status', 'pending')->count();
        if ($pendingReviews > 0) {
            $notifications[] = [
                'label' => "{$pendingReviews} review".($pendingReviews > 1 ? 's' : '').' awaiting approval',
                'url' => route('admin.reviews.index', ['status' => 'pending']),
                'icon' => 'review',
            ];
        }

        $lowStock = Product::lowStock()->where('is_active', true)->count();
        if ($lowStock > 0) {
            $notifications[] = [
                'label' => "{$lowStock} product".($lowStock > 1 ? 's' : '').' running low on stock',
                'url' => route('admin.inventory.index', ['stock_status' => 'low_stock']),
                'icon' => 'stock',
            ];
        }

        $newContacts = ContactMessage::where('status', 'new')->count();
        if ($newContacts > 0) {
            $notifications[] = [
                'label' => "{$newContacts} new contact request".($newContacts > 1 ? 's' : ''),
                'url' => route('admin.contacts.index', ['status' => 'new']),
                'icon' => 'contact',
            ];
        }

        return $notifications;
    }
}

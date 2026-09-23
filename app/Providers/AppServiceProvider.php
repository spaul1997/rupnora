<?php

namespace App\Providers;

use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\WebsiteSetting;
use App\Observers\ContactMessageObserver;
use App\Observers\OrderObserver;
use App\Support\StorefrontCatalog;
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

        View::composer('components.layout.footer', function ($view) {
            $settings = WebsiteSetting::current();
            $icons = [
                'facebook' => 'M14 9h3V6h-3c-1.7 0-3 1.3-3 3v2H9v3h2v7h3v-7h3l1-3h-4V9c0-.6.4-1 1-1z',
                'instagram' => 'M12 2c2.7 0 3 0 4.1.06 1.1.05 1.8.22 2.5.47.7.27 1.2.6 1.8 1.16.6.6.9 1.1 1.16 1.8.25.7.42 1.4.47 2.5.06 1.1.06 1.4.06 4.1s0 3-.06 4.1c-.05 1.1-.22 1.8-.47 2.5a5 5 0 01-1.16 1.8 5 5 0 01-1.8 1.16c-.7.25-1.4.42-2.5.47-1.1.06-1.4.06-4.1.06s-3 0-4.1-.06c-1.1-.05-1.8-.22-2.5-.47a5 5 0 01-1.8-1.16 5 5 0 01-1.16-1.8c-.25-.7-.42-1.4-.47-2.5C2 15 2 14.7 2 12s0-3 .06-4.1c.05-1.1.22-1.8.47-2.5.27-.7.6-1.2 1.16-1.8.6-.6 1.1-.9 1.8-1.16.7-.25 1.4-.42 2.5-.47C9 2 9.3 2 12 2zm0 5a5 5 0 100 10 5 5 0 000-10zm0 8.2a3.2 3.2 0 110-6.4 3.2 3.2 0 010 6.4zm5.3-8.4a1.2 1.2 0 100-2.4 1.2 1.2 0 000 2.4z',
                'linkedin' => 'M6.5 8.5H3.2V19h3.3V8.5zM4.9 3A1.9 1.9 0 104.9 6.8 1.9 1.9 0 004.9 3zM20.8 13c0-3.2-1.7-4.7-4-4.7a3.5 3.5 0 00-3.2 1.8V8.5h-3.3V19h3.3v-5.2c0-1.4.3-2.7 2-2.7 1.7 0 1.8 1.6 1.8 2.8V19h3.3l.1-6z',
                'youtube' => 'M22 7.2a2.8 2.8 0 00-2-2C18.2 4.7 12 4.7 12 4.7s-6.2 0-8 .5a2.8 2.8 0 00-2 2A29 29 0 001.5 12 29 29 0 002 16.8a2.8 2.8 0 002 2c1.8.5 8 .5 8 .5s6.2 0 8-.5a2.8 2.8 0 002-2 29 29 0 00.5-4.8 29 29 0 00-.5-4.8zM10 15.2V8.8l5.5 3.2-5.5 3.2z',
            ];
            $labels = [
                'facebook' => 'Facebook',
                'instagram' => 'Instagram',
                'linkedin' => 'LinkedIn',
                'youtube' => 'YouTube',
            ];
            $socialLinks = collect([
                'facebook' => $settings->facebook,
                'instagram' => $settings->instagram,
                'linkedin' => $settings->linkedin,
                'youtube' => $settings->youtube,
            ])->filter(fn ($url) => filled($url));

            $view->with([
                'footerCategories' => StorefrontCatalog::topCategoriesByProductCount(4),
                'footerSocialLinks' => $socialLinks
                    ->map(fn ($url, $name) => [
                        'url' => $url,
                        'label' => $labels[$name],
                        'icon' => $icons[$name],
                    ])
                    ->all(),
            ]);
        });

        View::composer('components.layouts.app', function ($view) {
            $view->with('socialProofItems', StorefrontCatalog::socialProofItems());
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

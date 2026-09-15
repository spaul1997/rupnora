<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\HomeBannerController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\JewelleryCollectionController;
use App\Http\Controllers\Admin\JewelleryTypeController;
use App\Http\Controllers\Admin\MetalTypeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\WebsiteSettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {

    // Guest-only admin auth routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    });

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    Route::middleware(['auth', 'admin'])->group(function () {

        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // Products
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
        Route::patch('products/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('products.toggle-active');
        Route::patch('products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
        Route::delete('products/{product}/images/{image}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');
        Route::patch('products/{product}/images/{image}/primary', [ProductController::class, 'setPrimaryImage'])->name('products.images.primary');
        Route::get('products-export', [ProductController::class, 'export'])->name('products.export');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::patch('categories/{category}/toggle-active', [CategoryController::class, 'toggleActive'])->name('categories.toggle-active');
        Route::resource('jewellery-types', JewelleryTypeController::class)->parameters(['jewellery-types' => 'jewelleryType'])->except(['show']);
        Route::patch('jewellery-types/{jewelleryType}/toggle-active', [JewelleryTypeController::class, 'toggleActive'])->name('jewellery-types.toggle-active');
        Route::resource('metal-types', MetalTypeController::class)->parameters(['metal-types' => 'metalType'])->except(['show']);
        Route::patch('metal-types/{metalType}/toggle-active', [MetalTypeController::class, 'toggleActive'])->name('metal-types.toggle-active');
        Route::resource('jewellery-collections', JewelleryCollectionController::class)->parameters(['jewellery-collections' => 'jewelleryCollection'])->except(['show']);
        Route::patch('jewellery-collections/{jewelleryCollection}/toggle-active', [JewelleryCollectionController::class, 'toggleActive'])->name('jewellery-collections.toggle-active');

        // Inventory
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::get('inventory/history/{product}', [InventoryController::class, 'history'])->name('inventory.history');

        // Orders
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::patch('orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('orders.update-payment-status');
        Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('orders-export', [OrderController::class, 'export'])->name('orders.export');

        // Customers
        Route::resource('customers', CustomerController::class)->only(['index', 'show', 'edit', 'update']);
        Route::patch('customers/{customer}/toggle-active', [CustomerController::class, 'toggleActive'])->name('customers.toggle-active');

        // Reviews
        Route::resource('reviews', ReviewController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::patch('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
        Route::patch('reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
        Route::patch('reviews/{review}/hide', [ReviewController::class, 'hide'])->name('reviews.hide');
        Route::post('reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');

        // Feedback
        Route::resource('feedbacks', FeedbackController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::patch('feedbacks/{feedback}/assign', [FeedbackController::class, 'assign'])->name('feedbacks.assign');
        Route::patch('feedbacks/{feedback}/resolve', [FeedbackController::class, 'resolve'])->name('feedbacks.resolve');
        Route::patch('feedbacks/{feedback}/close', [FeedbackController::class, 'close'])->name('feedbacks.close');
        Route::post('feedbacks/{feedback}/note', [FeedbackController::class, 'addNote'])->name('feedbacks.note');

        // Contact requests
        Route::resource('contacts', ContactController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::post('contacts/{contact}/reply', [ContactController::class, 'reply'])->name('contacts.reply');
        Route::patch('contacts/{contact}/assign', [ContactController::class, 'assign'])->name('contacts.assign');
        Route::patch('contacts/{contact}/priority', [ContactController::class, 'updatePriority'])->name('contacts.update-priority');
        Route::patch('contacts/{contact}/status', [ContactController::class, 'updateStatus'])->name('contacts.update-status');
        Route::patch('contacts/{contact}/close', [ContactController::class, 'close'])->name('contacts.close');

        // FAQs
        Route::resource('faqs', FaqController::class)->except(['show']);
        Route::patch('faqs/{faq}/toggle-active', [FaqController::class, 'toggleActive'])->name('faqs.toggle-active');

        // Coupons
        Route::resource('home-banners', HomeBannerController::class)->parameters(['home-banners' => 'homeBanner'])->except(['show']);
        Route::patch('home-banners/{homeBanner}/toggle-active', [HomeBannerController::class, 'toggleActive'])->name('home-banners.toggle-active');
        Route::resource('coupons', CouponController::class)->except(['show']);
        Route::patch('coupons/{coupon}/toggle-active', [CouponController::class, 'toggleActive'])->name('coupons.toggle-active');

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/orders', [ReportController::class, 'orders'])->name('reports.orders');
        Route::get('reports/products', [ReportController::class, 'products'])->name('reports.products');
        Route::get('reports/categories', [ReportController::class, 'categories'])->name('reports.categories');
        Route::get('reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
        Route::get('reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('reports/low-stock', [ReportController::class, 'lowStock'])->name('reports.low-stock');
        Route::get('reports/payments', [ReportController::class, 'payments'])->name('reports.payments');
        Route::get('reports/refunds', [ReportController::class, 'refunds'])->name('reports.refunds');
        Route::get('reports/{type}/export/{format}', [ReportController::class, 'export'])->name('reports.export');

        // Settings
        Route::get('settings', [WebsiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [WebsiteSettingController::class, 'update'])->name('settings.update');
    });
});

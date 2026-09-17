<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthPageController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Mail\ForgotPasswordOtpMail;
use App\Mail\OrderFailedMail;
use App\Mail\OrderSuccessMail;
use App\Mail\PasswordResetSuccessMail;
use App\Mail\PaymentFailedMail;
use App\Mail\PaymentRefundMail;
use App\Mail\PaymentSuccessMail;
use App\Mail\RefundAcceptedMail;
use App\Mail\RefundCompletedMail;
use App\Mail\RefundRejectedMail;
use App\Mail\RegisterMail;
use App\Models\Order;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');

    return redirect()
        ->back()
        ->with('success', 'Application cache cleared successfully.');
})->name('clear-cache');

Route::get('/mail-test', function () {
    try {
        $supportEmail = WebsiteSetting::current()->support_email;

        abort_unless($supportEmail, 422, 'Set a support email in Website Settings first.');

        Mail::raw('Rupnora webmail SMTP is working successfully.', function ($message) use ($supportEmail) {
            $message->to($supportEmail)
                ->subject('Rupnora Laravel Mail Test');
        });

        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully.',
        ]);
    } catch (Throwable $exception) {
        report($exception);

        return response()->json([
            'success' => false,
            'message' => 'Email sending failed. Check storage/logs/laravel.log',
        ], 500);
    }
});

Route::get('/mail-preview/{type}', function (string $type) {
    $user = new User(['name' => 'Ananya Rao', 'email' => 'ananya.rao@example.com']);

    if ($type === 'register') {
        return new RegisterMail($user);
    }

    if ($type === 'forgot-password-otp') {
        return new ForgotPasswordOtpMail('4821', $user->name);
    }

    if ($type === 'password-reset-success') {
        return new PasswordResetSuccessMail($user->name);
    }

    $order = Order::with('items')->latest()->first();

    if (! $order) {
        abort(404, 'No orders found to preview with. Seed an order first.');
    }

    return match ($type) {
        'order-success' => new OrderSuccessMail($order),
        'order-failed' => new OrderFailedMail($order, 'Your bank declined the transaction.'),
        'payment-success' => new PaymentSuccessMail($order),
        'payment-failed' => new PaymentFailedMail($order, 'The transaction timed out.'),
        'payment-refund' => new PaymentRefundMail($order),
        'refund-accepted' => new RefundAcceptedMail($order),
        'refund-rejected' => new RefundRejectedMail($order, 'The item shows signs of use and does not meet our return policy.'),
        'refund-completed' => new RefundCompletedMail($order),
        default => abort(404, 'Unknown mail preview type.'),
    };
})->name('mail-preview');

Route::get('/', HomeController::class)->name('home');

Route::get('/about', function () {
    return view('pages.about', ['title' => 'Our Story']);
})->name('about');

Route::get('/careers', function () {
    return view('pages.careers', ['title' => 'Careers']);
})->name('careers');

Route::get('/influencer', function () {
    return view('pages.influencer', ['title' => 'Influencer Program']);
})->name('influencer');

Route::get('/privacy-policy', function () {
    return view('pages.privacy-policy', ['title' => 'Privacy Policy']);
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    return view('pages.terms-of-service', ['title' => 'Terms of Service']);
})->name('terms-of-service');

Route::get('/refund-policy', function () {
    return view('pages.refund-policy', ['title' => 'Refund Policy']);
})->name('refund-policy');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
Route::get('/collections/{slug}', [CollectionController::class, 'show'])->name('collection.show');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/new-arrivals', [CategoryController::class, 'newArrivals'])->name('new-arrivals');
Route::get('/best-sellers', [CategoryController::class, 'bestSellers'])->name('best-sellers');
Route::get('/jewellery-type/{slug}', [CategoryController::class, 'jewelleryType'])->name('jewellery-type.show');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');

Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{key}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/cart/{key}/wishlist', [CartController::class, 'moveToWishlist'])->name('cart.move-to-wishlist');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout/addresses', [CheckoutController::class, 'storeAddress'])->name('checkout.addresses.store');
Route::post('/checkout/order', [CheckoutController::class, 'placeOrder'])->name('checkout.order.store');
Route::get('/order-success/{id?}', [CheckoutController::class, 'success'])->name('order.success');

Route::get('/login', [AuthPageController::class, 'login'])->name('login');
Route::post('/login', [AuthPageController::class, 'loginStore'])->name('login.store');
Route::post('/logout', [AuthPageController::class, 'logout'])->name('logout');
Route::get('/register', [AuthPageController::class, 'register'])->name('register');
Route::post('/register', [AuthPageController::class, 'registerStore'])->name('register.store');
Route::get('/forgot-password', [AuthPageController::class, 'forgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthPageController::class, 'sendResetCode'])->middleware('throttle:6,1')->name('password.email');
Route::post('/forgot-password/verify', [AuthPageController::class, 'verifyResetCode'])->middleware('throttle:6,1')->name('password.verify');
Route::post('/reset-password', [AuthPageController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');

require __DIR__.'/admin.php';

Route::prefix('account')->name('account.')->group(function () {
    Route::get('/wishlist', [AccountController::class, 'wishlist'])->name('wishlist');
    Route::post('/wishlist', [AccountController::class, 'addWishlist'])->name('wishlist.store');
    Route::delete('/wishlist/{product}', [AccountController::class, 'removeWishlist'])->name('wishlist.destroy');

    Route::middleware('storefront.customer')->group(function () {
        Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{id}', [AccountController::class, 'orderShow'])->name('orders.show');
        Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');
        Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
        Route::get('/change-password', [AccountController::class, 'changePassword'])->name('change-password');
        Route::get('/notifications', [AccountController::class, 'notifications'])->name('notifications');
        Route::get('/support', [AccountController::class, 'support'])->name('support');
    });
});

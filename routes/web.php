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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');

    return redirect()
        ->back()
        ->with('success', 'Application cache cleared successfully.');
})->name('clear-cache');

Route::get('/', HomeController::class)->name('home');

Route::get('/about', function () {
    return view('pages.about', ['title' => 'Our Story']);
})->name('about');

Route::get('/careers', function () {
    return view('pages.careers', ['title' => 'Careers']);
})->name('careers');

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
Route::get('/forgot-password', [AuthPageController::class, 'forgotPassword'])->name('password.request');

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

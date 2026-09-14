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
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');

Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/cart', [CartController::class, 'index'])->name('cart');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::get('/order-success/{id?}', [CheckoutController::class, 'success'])->name('order.success');

Route::get('/login', [AuthPageController::class, 'login'])->name('login');
Route::get('/register', [AuthPageController::class, 'register'])->name('register');
Route::get('/forgot-password', [AuthPageController::class, 'forgotPassword'])->name('password.request');

require __DIR__.'/admin.php';

Route::prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [AccountController::class, 'orderShow'])->name('orders.show');
    Route::get('/wishlist', [AccountController::class, 'wishlist'])->name('wishlist');
    Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::get('/change-password', [AccountController::class, 'changePassword'])->name('change-password');
    Route::get('/notifications', [AccountController::class, 'notifications'])->name('notifications');
    Route::get('/support', [AccountController::class, 'support'])->name('support');
});

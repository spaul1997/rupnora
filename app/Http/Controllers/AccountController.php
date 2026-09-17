<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorefrontAddressRequest;
use App\Models\Faq;
use App\Models\Order;
use App\Models\Product;
use App\Models\WebsiteSetting;
use App\Notifications\AccountActivity;
use App\Support\CheckoutAddresses;
use App\Support\CheckoutOrders;
use App\Support\ShoppingCart;
use App\Support\StorefrontCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
    protected function customer(): array
    {
        $customer = Auth::user();
        if (! $customer) {
            return [];
        }
        $name = $customer->name;
        $nameParts = preg_split('/\s+/', trim($name), 2);

        return [
            'name' => $name,
            'first_name' => $nameParts[0] ?? '',
            'last_name' => $nameParts[1] ?? '',
            'email' => $customer->email,
            'phone' => $customer->phone,
            'joined' => $customer->created_at->format('F Y'),
            'date_of_birth' => $customer->date_of_birth?->format('Y-m-d'),
            'has_photo' => filled($customer->profile_photo_path),
        ];
    }

    public function dashboard()
    {
        $orders = CheckoutOrders::all();
        $addresses = CheckoutAddresses::all();
        $customer = $this->customer();
        $profileFields = [$customer['name'], $customer['email'], $customer['phone'], $customer['date_of_birth'], $customer['has_photo'], count($addresses)];

        return view('account.dashboard', [
            'title' => 'My Account',
            'customer' => $customer,
            'orders' => array_slice($orders, 0, 3),
            'totalOrders' => count($orders),
            'activeOrders' => collect($orders)->whereIn('status', ['Pending', 'Processing', 'Confirmed', 'Packed', 'Shipped', 'Out for Delivery'])->count(),
            'wishlistCount' => count(ShoppingCart::wishlistIds()),
            'address' => $addresses[0] ?? null,
            'addressCount' => count($addresses),
            'profileCompletion' => (int) round(count(array_filter($profileFields)) / count($profileFields) * 100),
            'recommended' => StorefrontCatalog::bestSellers(4),
        ]);
    }

    public function orders()
    {
        return view('account.orders', [
            'title' => 'My Orders',
            'customer' => $this->customer(),
            'orders' => CheckoutOrders::all(),
        ]);
    }

    public function orderShow(string $id)
    {
        $order = CheckoutOrders::find($id);

        abort_if(! $order, Response::HTTP_NOT_FOUND);

        return view('account.order-details', [
            'title' => 'Order '.$order['id'],
            'customer' => $this->customer(),
            'order' => $order,
            'subtotal' => $order['subtotal'],
            'shipping' => $order['shipping'],
            'tax' => $order['tax'],
        ]);
    }

    public function wishlist()
    {
        $ids = ShoppingCart::wishlistIds();

        return view('account.wishlist', [
            'title' => 'Wishlist',
            'customer' => $this->customer(),
            'products' => collect($ids)->map(fn ($id) => StorefrontCatalog::product($id))->filter()->values()->all(),
        ]);
    }

    public function removeWishlist(string $product): JsonResponse
    {
        ShoppingCart::removeWishlistId($product);
        $wishlistIds = ShoppingCart::wishlistIds();

        return response()->json([
            'count' => count($wishlistIds),
            'wishlistIds' => $wishlistIds,
            'message' => 'Removed from wishlist',
        ]);
    }

    public function addWishlist(Request $request): JsonResponse
    {
        if (is_int($request->input('product_id'))) {
            $request->merge(['product_id' => (string) $request->input('product_id')]);
        }
        $data = $request->validate([
            'product_id' => ['required', 'string', 'max:255'],
        ]);

        abort_if(! ShoppingCart::addWishlistId($data['product_id']), Response::HTTP_NOT_FOUND);

        $wishlistIds = ShoppingCart::wishlistIds();

        return response()->json([
            'count' => count($wishlistIds),
            'wishlistIds' => $wishlistIds,
            'message' => 'Added to wishlist',
        ]);
    }

    public function addresses()
    {
        return view('account.addresses', [
            'title' => 'Saved Addresses',
            'customer' => $this->customer(),
            'addresses' => CheckoutAddresses::all(),
            'editingAddress' => null,
        ]);
    }

    public function editAddress(string $address)
    {
        $editingAddress = Auth::user()->addresses()->findOrFail($address)->toStorefront();

        return view('account.addresses', [
            'title' => 'Edit Address', 'customer' => $this->customer(),
            'addresses' => CheckoutAddresses::all(), 'editingAddress' => $editingAddress,
        ]);
    }

    public function storeAddress(StorefrontAddressRequest $request)
    {
        CheckoutAddresses::add($request->validated());

        return redirect()->route('account.addresses')->with('success', 'Address saved successfully.');
    }

    public function updateAddress(StorefrontAddressRequest $request, string $address)
    {
        DB::transaction(function () use ($request, $address) {
            $customer = Auth::user();
            $customer->newQuery()->whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $saved = $customer->addresses()->findOrFail($address);
            $data = $request->validated();
            if ($request->boolean('default')) {
                $customer->addresses()->update(['is_default' => false]);
                $saved->refresh();
            }
            $saved->update([...collect($data)->except('default')->all(), 'is_default' => $saved->is_default || $request->boolean('default')]);
        });

        return redirect()->route('account.addresses')->with('success', 'Address updated successfully.');
    }

    public function deleteAddress(string $address)
    {
        DB::transaction(function () use ($address) {
            $customer = Auth::user();
            $customer->newQuery()->whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $saved = $customer->addresses()->findOrFail($address);
            $wasDefault = $saved->is_default;
            $saved->delete();
            if ($wasDefault) {
                $customer->addresses()->oldest('id')->first()?->update(['is_default' => true]);
            }
        });

        return redirect()->route('account.addresses')->with('success', 'Address deleted.');
    }

    public function defaultAddress(string $address)
    {
        DB::transaction(function () use ($address) {
            $customer = Auth::user();
            $customer->newQuery()->whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $saved = $customer->addresses()->findOrFail($address);
            $customer->addresses()->update(['is_default' => false]);
            $customer->addresses()->whereKey($saved->id)->update(['is_default' => true]);
        });

        return redirect()->route('account.addresses')->with('success', 'Default address updated.');
    }

    public function profile()
    {
        return view('account.profile', [
            'title' => 'Profile',
            'customer' => $this->customer(),
        ]);
    }

    public function changePassword()
    {
        return view('account.change-password', [
            'title' => 'Change Password',
            'customer' => $this->customer(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => mb_strtolower(trim($request->input('email')))]);
        }
        $customer = Auth::user();
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['nullable', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($customer->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users')->ignore($customer->id)],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);
        $attributes = [
            'name' => trim($data['first_name'].' '.($data['last_name'] ?? '')),
            'email' => $data['email'], 'phone' => $data['phone'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
        ];
        if ($customer->email !== $data['email']) {
            $attributes['email_verified_at'] = null;
        }
        $previousPhoto = $customer->profile_photo_path;
        if ($request->hasFile('photo')) {
            $attributes['profile_photo_path'] = $request->file('photo')->store('profile-photos', 'local');
            if (! $attributes['profile_photo_path']) {
                throw ValidationException::withMessages(['photo' => 'Unable to save the photo. Please try again.']);
            }
        }
        $customer->forceFill($attributes)->save();
        if (isset($attributes['profile_photo_path']) && $previousPhoto) {
            Storage::disk('local')->delete($previousPhoto);
        }
        $request->session()->put('storefront_customer', $customer->only(['id', 'name', 'email', 'phone']));

        return redirect()->route('account.profile')->with('success', 'Profile updated successfully.');
    }

    public function photo()
    {
        $path = Auth::user()->profile_photo_path;
        abort_unless($path && Storage::disk('local')->exists($path), Response::HTTP_NOT_FOUND);

        return response()->file(Storage::disk('local')->path($path), ['Cache-Control' => 'private, no-store']);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(8)],
        ]);
        $customer = Auth::user();
        $customer->update(['password' => $data['password']]);
        $customer->setRememberToken(Str::random(60));
        $customer->save();
        DB::table('password_reset_tokens')->where('email', $customer->email)->delete();
        $request->session()->regenerate();
        $customer->notify(new AccountActivity('Password changed', 'Your account password was updated.'));

        return redirect()->route('account.change-password')->with('success', 'Password updated successfully.');
    }

    public function notifications()
    {
        $notifications = Auth::user()->notifications()->paginate(20);

        return view('account.notifications', [
            'title' => 'Notifications',
            'customer' => $this->customer(),
            'notifications' => $notifications,
        ]);
    }

    public function readNotification(string $notification)
    {
        Auth::user()->notifications()->findOrFail($notification)->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function readAllNotifications()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function support()
    {
        return view('account.support', [
            'title' => 'Support',
            'customer' => $this->customer(),
            'faqs' => Faq::active()->orderBy('sort_order')->get()->map(fn ($faq) => ['q' => $faq->question, 'a' => $faq->answer])->all(),
            'settings' => WebsiteSetting::current(),
            'tickets' => Auth::user()->supportMessages()->latest()->paginate(10),
        ]);
    }

    public function storeSupport(Request $request)
    {
        $data = $request->validate(['subject' => ['required', 'string', 'max:150'], 'message' => ['required', 'string', 'max:2000']]);
        $customer = Auth::user();
        $ticket = $customer->supportMessages()->create([
            ...$data, 'ticket_no' => 'CNT-'.now()->year.'-'.Str::upper(Str::random(12)),
            'name' => $customer->name, 'email' => $customer->email, 'phone' => $customer->phone,
            'priority' => 'normal', 'status' => 'new',
        ]);

        return redirect()->route('account.support')->with('success', 'Support request '.$ticket->ticket_no.' submitted.');
    }

    public function invoice(string $id)
    {
        $order = Auth::user()->orders()->where('order_number', $id)->with('items')->firstOrFail();

        return view('account.invoice', ['order' => $order, 'settings' => WebsiteSetting::current()]);
    }

    public function cancelOrder(string $id)
    {
        $refundRequired = DB::transaction(function () use ($id) {
            $order = Auth::user()->orders()->where('order_number', $id)->lockForUpdate()->firstOrFail();
            if (! in_array($order->status, ['pending', 'confirmed', 'processing'])) {
                throw ValidationException::withMessages(['order' => 'This order can no longer be cancelled. Please contact support.']);
            }
            $this->restoreOrderStock($order);
            $order->update(['status' => 'cancelled']);
            $order->statusHistories()->create(['status' => 'cancelled', 'remark' => 'Cancelled by customer.', 'updated_by' => Auth::id()]);

            return $order->paid_amount > 0;
        });

        return back()->with('success', 'Order cancelled.'.($refundRequired ? ' Contact support to arrange your refund.' : ''));
    }

    private function restoreOrderStock(Order $order): void
    {
        if (! $order->stock_reserved) {
            return;
        }
        $quantities = $order->items()->get()->groupBy('product_id')->map(fn ($items) => $items->sum('quantity'));
        $products = Product::whereIn('id', $quantities->keys())->orderBy('id')->lockForUpdate()->get();
        foreach ($products as $product) {
            $product->stock_quantity += $quantities[$product->id];
            $product->save();
        }
        $order->stock_reserved = false;
    }

    public function requestReturn(string $id)
    {
        DB::transaction(function () use ($id) {
            $order = Auth::user()->orders()->where('order_number', $id)->lockForUpdate()->firstOrFail();
            if ($order->status !== 'delivered') {
                throw ValidationException::withMessages(['order' => 'Returns can only be requested for delivered orders.']);
            }
            $order->update(['status' => 'return_requested']);
            $order->statusHistories()->create(['status' => 'return_requested', 'remark' => 'Return requested by customer. Please contact the customer to arrange collection.', 'updated_by' => Auth::id()]);
        });

        return back()->with('success', 'Return requested. Our team will contact you with the next steps.');
    }

    public function buyAgain(string $id)
    {
        $order = Auth::user()->orders()->where('order_number', $id)->with('items.product')->firstOrFail();
        $added = 0;
        foreach ($order->items as $item) {
            if ($item->product?->is_active && $item->product->stock_quantity > 0 && ShoppingCart::add($item->product_id, $item->quantity, $item->size)) {
                $added++;
            }
        }

        return redirect()->route('cart')->with($added ? 'success' : 'error', $added ? 'Available order items added to your cart.' : 'These items are currently unavailable.');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Faq;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\AccountActivity;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use DatabaseMigrations;

    public function test_account_pages_render_real_empty_states_and_require_an_active_customer(): void
    {
        $customer = $this->customer();
        foreach (['dashboard', 'orders', 'addresses', 'profile', 'change-password', 'notifications', 'support'] as $page) {
            $this->get(route('account.'.$page))->assertRedirect(route('login'));
        }
        $this->get(route('account.wishlist'))->assertOk();
        $this->actingAs($customer);
        foreach (['dashboard', 'orders', 'wishlist', 'addresses', 'profile', 'change-password', 'notifications', 'support'] as $page) {
            $this->get(route('account.'.$page))->assertOk();
        }
        $this->get(route('account.dashboard'))->assertViewHas('totalOrders', 0)->assertViewHas('address', null)->assertViewHas('wishlistCount', 0);
        $this->get(route('account.notifications'))->assertSee('No notifications');
        $this->actingAs($this->customer(['is_active' => false]))->get(route('account.dashboard'))->assertRedirect(route('login'));
        $this->actingAs($this->customer(['role' => 'admin']))->get(route('account.dashboard'))->assertRedirect(route('login'));
    }

    public function test_profile_changes_persist_and_do_not_change_account_permissions(): void
    {
        $customer = $this->customer(['email_verified_at' => now()]);
        $this->actingAs($customer)->patch(route('account.profile.update'), [
            'first_name' => 'Updated', 'last_name' => 'Customer', 'email' => 'UPDATED@example.com',
            'phone' => '+91 99999 12345', 'date_of_birth' => '1995-05-12', 'role' => 'admin',
        ])->assertRedirect(route('account.profile'))->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['id' => $customer->id, 'name' => 'Updated Customer', 'email' => 'updated@example.com', 'role' => 'customer', 'email_verified_at' => null]);
        $this->get(route('account.profile'))->assertSee('1995-05-12')->assertSee('Updated');
        $other = $this->customer();
        $this->patch(route('account.profile.update'), ['first_name' => 'Updated', 'email' => $other->email, 'date_of_birth' => now()->addDay()->toDateString()])
            ->assertSessionHasErrors(['email', 'date_of_birth']);
        $this->assertSame('updated@example.com', $customer->fresh()->email);
    }

    public function test_profile_photo_is_saved_privately_and_can_be_replaced(): void
    {
        Storage::fake('local');
        $customer = $this->customer();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j5AAAAABJRU5ErkJggg==');
        $data = ['first_name' => 'Photo', 'email' => $customer->email, 'photo' => UploadedFile::fake()->createWithContent('avatar.png', $png)];
        $this->actingAs($customer)->patch(route('account.profile.update'), $data)->assertSessionHasNoErrors();
        $first = $customer->fresh()->profile_photo_path;
        Storage::disk('local')->assertExists($first);
        $this->get(route('account.photo'))->assertOk();
        $data['photo'] = UploadedFile::fake()->createWithContent('replacement.png', $png);
        $this->patch(route('account.profile.update'), $data)->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($first);
        Storage::disk('local')->assertExists($customer->fresh()->profile_photo_path);
        $this->actingAs($this->customer())->get(route('account.photo'))->assertNotFound();
    }

    public function test_password_change_checks_current_password_and_confirmation(): void
    {
        $customer = $this->customer(['password' => 'OldPassword1!']);
        $this->actingAs($customer)->put(route('account.password.update'), ['current_password' => 'wrong', 'password' => 'NewPassword1!', 'password_confirmation' => 'NewPassword1!'])
            ->assertSessionHasErrors('current_password');
        $this->put(route('account.password.update'), ['current_password' => 'OldPassword1!', 'password' => 'NewPassword1!', 'password_confirmation' => 'DifferentPassword1!'])
            ->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('OldPassword1!', $customer->fresh()->password));
        $this->put(route('account.password.update'), ['current_password' => 'OldPassword1!', 'password' => 'NewPassword1!', 'password_confirmation' => 'NewPassword1!'])
            ->assertRedirect(route('account.change-password'))->assertSessionHas('success');
        $this->assertTrue(Hash::check('NewPassword1!', $customer->fresh()->password));
        $this->assertSame('Password changed', $customer->notifications()->first()->data['title']);
    }

    public function test_addresses_persist_share_checkout_and_keep_one_default(): void
    {
        $customer = $this->customer();
        $this->actingAs($customer)->post(route('account.addresses.store'), $this->address())->assertRedirect(route('account.addresses'));
        $first = $customer->addresses()->firstOrFail();
        $this->assertTrue($first->is_default);
        $this->postJson(route('checkout.addresses.store'), $this->address(['type' => 'Office', 'line1' => 'Office Street', 'email' => 'delivery@example.com', 'district' => 'Kolkata']))->assertOk();
        $second = $customer->addresses()->latest('id')->firstOrFail();
        $this->get(route('checkout'))->assertViewHas('addresses', fn ($addresses) => count($addresses) === 2);
        $this->patch(route('account.addresses.default', $second->id))->assertSessionHasNoErrors();
        // Selecting the current default twice must preserve it after the bulk reset.
        $this->patch(route('account.addresses.default', $second->id))->assertSessionHasNoErrors();
        $this->assertSame(1, $customer->addresses()->where('is_default', true)->count());
        $this->assertTrue($second->fresh()->is_default);
        $this->get(route('account.addresses.edit', $second->id))->assertOk()->assertSee('Office Street');
        $this->put(route('account.addresses.update', $second->id), $this->address(['line1' => 'Updated Street', 'default' => '1']))->assertRedirect(route('account.addresses'));
        $this->assertTrue($second->fresh()->is_default);
        $this->assertSame('Updated Street', $second->fresh()->line1);
        $this->delete(route('account.addresses.destroy', $second->id))->assertRedirect(route('account.addresses'));
        $this->assertTrue($first->fresh()->is_default);
        $this->post(route('account.addresses.store'), $this->address(['pincode' => 'bad']))->assertSessionHasErrors('pincode');
    }

    public function test_customers_cannot_read_or_change_another_customers_addresses(): void
    {
        $owner = $this->customer();
        $address = $owner->addresses()->create([...$this->address(), 'is_default' => true]);
        $this->actingAs($this->customer())->get(route('account.addresses.edit', $address->id))->assertNotFound();
        $this->put(route('account.addresses.update', $address->id), $this->address())->assertNotFound();
        $this->patch(route('account.addresses.default', $address->id))->assertNotFound();
        $this->delete(route('account.addresses.destroy', $address->id))->assertNotFound();
        $this->assertNotNull($address->fresh());
    }

    public function test_wishlist_is_persistent_unique_and_scoped_to_each_customer(): void
    {
        $customer = $this->customer();
        $product = $this->product();
        $this->actingAs($customer)->postJson(route('account.wishlist.store'), ['product_id' => $product->id])->assertOk()->assertJsonPath('count', 1);
        $this->postJson(route('account.wishlist.store'), ['product_id' => $product->slug])->assertOk()->assertJsonPath('count', 1);
        $this->assertDatabaseCount('wishlist_items', 1);
        $this->actingAs($this->customer())->get(route('account.wishlist'))->assertViewHas('products', []);
        $this->deleteJson(route('account.wishlist.destroy', $product->slug))->assertOk()->assertJsonPath('count', 0);
        $this->assertDatabaseCount('wishlist_items', 1);
        $this->actingAs($customer)->get(route('account.wishlist'))->assertSee($product->name);
        $this->deleteJson(route('account.wishlist.destroy', $product->slug))->assertOk()->assertJsonPath('count', 0);
        $this->assertDatabaseCount('wishlist_items', 0);
    }

    public function test_orders_use_stored_totals_and_snapshots_and_are_private(): void
    {
        $owner = $this->customer();
        $product = $this->product();
        $order = $this->order($owner, $product);
        $product->update(['name' => 'Renamed Product', 'selling_price' => 9999]);
        $this->actingAs($owner)->get(route('account.orders.show', $order->order_number))->assertOk()
            ->assertSee('Original Product')->assertSee('5,499')->assertViewHas('tax', 123.0)
            ->assertViewHas('order', fn ($data) => $data['items'][0]['product']['price'] === 1500.0 && $data['shipping'] === 199.0);
        $this->get(route('account.orders.invoice', $order->order_number))->assertOk()->assertSee('5,499.00')->assertSee('Original Product');
        $product->delete();
        $this->get(route('account.orders.show', $order->order_number))->assertOk()->assertSee('Original Product');
        $this->actingAs($this->customer())->get(route('account.orders'))->assertViewHas('orders', []);
        $this->get(route('account.orders.show', $order->order_number))->assertNotFound();
        $this->get(route('account.orders.invoice', $order->order_number))->assertNotFound();
        foreach (['cancel', 'return', 'buy-again'] as $action) {
            $this->post(route('account.orders.'.$action, $order->order_number))->assertNotFound();
        }
        $this->get(route('order.success', $order->order_number))->assertRedirect(route('cart'));
    }

    public function test_checkout_creates_persistent_account_orders_and_cancellation_restores_stock_once(): void
    {
        $customer = $this->customer();
        $product = $this->product(['stock_quantity' => 10]);
        $address = $customer->addresses()->create([...$this->address(), 'is_default' => true]);
        $this->actingAs($customer)->withSession(['shopping_cart.initialized' => true, 'shopping_cart.items' => []]);
        $this->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 2])->assertOk();
        $response = $this->postJson(route('checkout.order.store'), ['address_id' => $address->id, 'delivery' => 'express', 'payment' => 'cod'])->assertOk();
        $order = $customer->orders()->firstOrFail();
        $this->assertSame($order->order_number, $response->json('orderId'));
        $this->assertSame('Cash on Delivery', $order->payment_method);
        $this->assertSame('cod', $order->payment_status);
        $this->assertSame(2, $order->items()->first()->quantity);
        $this->assertSame(8, $product->fresh()->stock_quantity);
        $this->get(route('account.dashboard'))->assertViewHas('totalOrders', 1)->assertViewHas('activeOrders', 1);
        $this->get(route('order.success', $order->order_number))->assertOk();
        $this->post(route('account.orders.cancel', $order->order_number))->assertSessionHas('success');
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->post(route('account.orders.cancel', $order->order_number))->assertSessionHasErrors('order');
        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->assertFalse($order->fresh()->stock_reserved);
    }

    public function test_checkout_accepts_online_payment_and_rejects_removed_payment_methods(): void
    {
        $customer = $this->customer();
        $product = $this->product();
        $address = $customer->addresses()->create([...$this->address(), 'is_default' => true]);
        $this->actingAs($customer)->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 1])->assertOk();

        foreach (['upi', 'card', 'netbanking', 'wallet'] as $payment) {
            $this->postJson(route('checkout.order.store'), [
                'address_id' => $address->id, 'delivery' => 'standard', 'payment' => $payment,
            ])->assertUnprocessable()->assertJsonValidationErrors('payment');
        }

        $this->assertDatabaseCount('orders', 0);
        $this->postJson(route('checkout.order.store'), [
            'address_id' => $address->id, 'delivery' => 'standard', 'payment' => 'online',
        ])->assertOk();

        $order = $customer->orders()->firstOrFail();
        $this->assertSame('Online Payment', $order->payment_method);
        $this->assertSame('pending', $order->payment_status);
        $this->assertNull($order->paid_at);
        $this->get(route('order.success', $order->order_number))->assertOk()
            ->assertViewHas('order', fn ($data) => $data['payment_method'] === 'Online Payment' && $data['payment_status'] === 'Pending');
    }

    public function test_guest_online_payment_is_saved_without_marking_an_unpaid_order_as_paid(): void
    {
        $product = $this->product();
        $addressId = $this->postJson(route('checkout.addresses.store'), $this->address([
            'email' => 'delivery@example.com', 'district' => 'Kolkata',
        ]))->assertOk()->json('address.id');
        $this->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 1])->assertOk();
        $orderId = $this->postJson(route('checkout.order.store'), [
            'address_id' => $addressId, 'delivery' => 'standard', 'payment' => 'online',
        ])->assertOk()->json('orderId');

        $this->get(route('order.success', $orderId))->assertOk()
            ->assertViewHas('order', fn ($order) => $order['payment_method'] === 'Online Payment'
                && $order['payment_status'] === 'Pending');
    }

    public function test_return_and_buy_again_actions_use_real_orders_and_stock(): void
    {
        $customer = $this->customer();
        $product = $this->product();
        $order = $this->order($customer, $product, ['status' => 'shipped']);
        $this->actingAs($customer)->post(route('account.orders.return', $order->order_number))->assertSessionHasErrors('order');
        $this->post(route('account.orders.cancel', $order->order_number))->assertSessionHasErrors('order');
        $order->update(['status' => 'delivered', 'delivered_at' => now()]);
        $this->post(route('account.orders.buy-again', $order->order_number))->assertRedirect(route('cart'));
        $this->get(route('cart'))->assertSee($product->name);
        $this->post(route('account.orders.return', $order->order_number))->assertSessionHas('success');
        $this->assertSame('return_requested', $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'status' => 'return_requested']);
        $this->post(route('account.orders.return', $order->order_number))->assertSessionHasErrors('order');
    }

    public function test_order_notifications_and_read_actions_are_persistent_and_private(): void
    {
        $owner = $this->customer();
        $order = $this->order($owner, $this->product());
        $order->update(['status' => 'shipped']);
        $this->assertSame(2, $owner->unreadNotifications()->count());
        $note = $owner->notifications()->latest()->first();
        $other = $this->customer();
        $other->notify(new AccountActivity('Private notification', 'Only for the other customer.'));
        $this->actingAs($other)->patch(route('account.notifications.read', $note->id))->assertNotFound();
        $this->actingAs($owner)->get(route('account.notifications'))->assertOk()->assertSee($order->order_number)->assertDontSee('Private notification');
        $this->patch(route('account.notifications.read', $note->id))->assertSessionHas('success');
        $this->assertSame(1, $owner->unreadNotifications()->count());
        $this->patch(route('account.notifications.read-all'))->assertSessionHas('success');
        $this->assertSame(0, $owner->unreadNotifications()->count());
        $this->assertSame(1, $other->unreadNotifications()->count());
    }

    public function test_admin_delivery_updates_notify_once_after_all_order_changes_commit(): void
    {
        $owner = $this->customer();
        $order = $this->order($owner, $this->product());
        $this->actingAs($this->customer(['role' => 'admin']))
            ->patch(route('admin.orders.update-status', $order), ['status' => 'delivered'])
            ->assertSessionHasNoErrors();
        $this->assertNotNull($order->fresh()->delivered_at);
        $this->assertSame(2, $owner->notifications()->count());
        $this->assertSame(1, $owner->notifications()->get()->filter(fn ($note) => $note->data['title'] === 'Order update: Delivered')->count());

        try {
            DB::transaction(function () use ($order) {
                $order->update(['status' => 'returned']);
                throw new \RuntimeException('Simulated rollback');
            });
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated rollback', $exception->getMessage());
        }
        $this->assertSame('delivered', $order->fresh()->status);
        $this->assertSame(2, $owner->notifications()->count());
    }

    public function test_support_requests_and_admin_replies_are_saved_and_private(): void
    {
        $owner = $this->customer();
        Faq::create(['question' => 'Account FAQ', 'answer' => 'An account answer.', 'is_active' => true]);
        Faq::create(['question' => 'Hidden FAQ', 'answer' => 'Hidden answer.', 'is_active' => false]);
        $this->actingAs($owner)->post(route('account.support.store'), ['subject' => 'My order question', 'message' => 'Please help with delivery.'])->assertRedirect(route('account.support'));
        $ticket = $owner->supportMessages()->firstOrFail();
        $this->assertSame($owner->email, $ticket->email);
        $ticket->update(['admin_reply' => 'Your parcel will arrive tomorrow.', 'admin_note' => 'Internal admin note', 'replied_at' => now()]);
        $this->get(route('account.support'))->assertOk()->assertSee('Account FAQ')->assertDontSee('Hidden FAQ')
            ->assertSee($ticket->ticket_no)->assertSee('Your parcel will arrive tomorrow.')->assertDontSee('Internal admin note');
        $this->assertSame('Support replied', $owner->notifications()->first()->data['title']);
        $this->actingAs($this->customer())->get(route('account.support'))->assertDontSee($ticket->ticket_no)->assertDontSee('Your parcel will arrive tomorrow.');
    }

    public function test_logout_invalidates_customer_authentication(): void
    {
        $this->actingAs($this->customer())->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get(route('account.profile'))->assertRedirect(route('login'));
    }

    private function customer(array $attributes = []): User
    {
        return User::factory()->create(['role' => 'customer', 'is_active' => true, ...$attributes]);
    }

    private function address(array $attributes = []): array
    {
        return ['type' => 'Home', 'name' => 'Account Customer', 'phone' => '9876543210', 'line1' => 'Test Street',
            'line2' => 'Test Area', 'landmark' => '', 'city' => 'Kolkata', 'state' => 'West Bengal', 'pincode' => '700001', 'country' => 'India', ...$attributes];
    }

    private function product(array $attributes = []): Product
    {
        $category = Category::firstOrCreate(['slug' => 'account-test'], ['name' => 'Account Test', 'is_active' => true]);

        return Product::create(['name' => 'Account Test Product', 'slug' => 'account-test-product', 'sku' => 'ACCOUNT-001',
            'category_id' => $category->id, 'jewellery_type' => 'Ring', 'metal_type' => 'Gold',
            'selling_price' => 1500, 'mrp' => 2000, 'stock_quantity' => 10, 'is_active' => true, ...$attributes]);
    }

    private function order(User $customer, Product $product, array $attributes = []): Order
    {
        $order = $customer->orders()->create(['order_number' => 'ORD-ACCOUNT-001', 'customer_name' => $customer->name,
            'customer_email' => $customer->email, 'status' => 'processing', 'payment_status' => 'paid', 'payment_method' => 'UPI',
            'subtotal' => 5877, 'discount_amount' => 500, 'coupon_discount' => 200, 'shipping_charge' => 199,
            'gst_amount' => 123, 'grand_total' => 5499, 'shipping_address' => $this->address(), ...$attributes]);
        $order->items()->create(['product_id' => $product->id, 'product_name' => 'Original Product', 'sku' => $product->sku,
            'quantity' => 2, 'price' => 1500, 'total' => 3000]);

        return $order;
    }
}

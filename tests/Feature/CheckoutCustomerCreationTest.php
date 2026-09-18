<?php

namespace Tests\Feature;

use App\Mail\OrderSuccessMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CheckoutCustomerCreationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_guest_checkout_creates_a_customer_with_a_hashed_mobile_password_and_persistent_order(): void
    {
        Mail::fake();
        WebsiteSetting::current()->update(['support_email' => 'support@example.com']);
        $product = $this->product();
        $addressId = $this->prepareCheckout($product, ['email' => 'BUYER@example.com']);
        $orderNumber = $this->placeOrder($addressId)->assertOk()->json('orderId');

        $customer = User::where('email', 'buyer@example.com')->firstOrFail();
        $this->assertSame('customer', $customer->role);
        $this->assertTrue($customer->is_active);
        $this->assertSame('9876543210', $customer->phone);
        $this->assertNotSame($customer->phone, $customer->password);
        $this->assertTrue(Hash::check('9876543210', $customer->password));
        $order = $customer->orders()->where('order_number', $orderNumber)->firstOrFail();
        $this->assertSame('buyer@example.com', $order->customer_email);
        $this->assertSame('9876543210', $order->customer_phone);
        $this->assertSame(2, $order->items()->firstOrFail()->quantity);
        $this->assertSame(8, $product->fresh()->stock_quantity);
        $this->assertSame(1, $customer->addresses()->count());
        $this->assertTrue($customer->addresses()->firstOrFail()->is_default);
        $this->assertGuest();
        $this->get(route('order.success', $orderNumber))->assertOk();

        $this->post(route('login.store'), ['login' => '+91 98765-43210', 'password' => '9876543210'])
            ->assertRedirect(route('account.dashboard'));
        $this->get(route('account.orders.show', $orderNumber))->assertOk();

        Mail::assertSent(OrderSuccessMail::class, function (OrderSuccessMail $mail) use ($orderNumber) {
            return $mail->order->order_number === $orderNumber
                && $mail->hasTo('buyer@example.com')
                && $mail->hasBcc('support@example.com');
        });
    }

    public function test_existing_customer_is_reused_by_email_without_changing_their_password_or_signing_in(): void
    {
        $customer = User::factory()->create([
            'email' => 'buyer@example.com', 'phone' => '9999999999', 'password' => 'ExistingPassword1!',
            'role' => 'customer', 'is_active' => true,
        ]);
        $originalPassword = $customer->password;
        $addressId = $this->prepareCheckout($this->product(), ['email' => 'BUYER@example.com']);
        $orderNumber = $this->placeOrder($addressId)->assertOk()->json('orderId');

        $this->assertSame(1, User::customers()->count());
        $this->assertSame($originalPassword, $customer->fresh()->password);
        $this->assertSame('9999999999', $customer->fresh()->phone);
        $this->assertDatabaseHas('orders', ['order_number' => $orderNumber, 'user_id' => $customer->id]);
        $this->assertGuest();
        $this->get(route('account.dashboard'))->assertRedirect(route('login'));
        $this->get(route('order.success', $orderNumber))->assertOk();
    }

    public function test_existing_customer_is_reused_by_formatted_mobile_number_with_checkout_contact_details_on_the_order(): void
    {
        $customer = User::factory()->create([
            'email' => 'existing@example.com', 'phone' => '+91 98765 43210', 'password' => 'ExistingPassword1!',
            'role' => 'customer', 'is_active' => true,
        ]);
        $addressId = $this->prepareCheckout($this->product());
        $orderNumber = $this->placeOrder($addressId)->assertOk()->json('orderId');

        $this->assertSame(1, User::customers()->count());
        $this->assertSame('existing@example.com', $customer->fresh()->email);
        $this->assertTrue(Hash::check('ExistingPassword1!', $customer->fresh()->password));
        $this->assertDatabaseHas('orders', [
            'order_number' => $orderNumber, 'user_id' => $customer->id,
            'customer_email' => 'buyer@example.com', 'customer_phone' => '9876543210',
        ]);
        $this->assertGuest();
        $this->get(route('order.success', $orderNumber))->assertOk()->assertDontSee('existing@example.com');
    }

    public function test_contact_details_matching_different_accounts_do_not_create_an_order(): void
    {
        User::factory()->create(['email' => 'buyer@example.com', 'phone' => '9999999999', 'role' => 'customer']);
        User::factory()->create(['email' => 'other@example.com', 'phone' => '9876543210', 'role' => 'customer']);
        $product = $this->product();
        $addressId = $this->prepareCheckout($product);

        $this->placeOrder($addressId)->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertSame(2, User::customers()->count());
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('customer_addresses', 0);
        $this->assertSame(10, $product->fresh()->stock_quantity);
    }

    public function test_guest_checkout_cannot_attach_an_order_to_an_admin_or_inactive_account(): void
    {
        $account = User::factory()->create(['email' => 'buyer@example.com', 'phone' => '9876543210', 'role' => 'admin']);
        $addressId = $this->prepareCheckout($this->product());

        $this->placeOrder($addressId)->assertUnprocessable()->assertJsonValidationErrors('email');
        $account->update(['role' => 'customer', 'is_active' => false]);
        $this->placeOrder($addressId)->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('customer_addresses', 0);
        $this->assertGuest();
    }

    public function test_creating_an_order_rolls_back_the_new_customer_and_address_when_saving_items_fails(): void
    {
        $product = $this->product();
        $addressId = $this->prepareCheckout($product);
        Event::listen('eloquent.creating: '.OrderItem::class, function () {
            throw ValidationException::withMessages(['order' => 'Unable to save order items.']);
        });

        $this->placeOrder($addressId)->assertUnprocessable()->assertJsonValidationErrors('order');
        $this->assertDatabaseMissing('users', ['email' => 'buyer@example.com']);
        $this->assertDatabaseCount('customer_addresses', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('notifications', 0);
        $this->assertSame(10, $product->fresh()->stock_quantity);
    }

    public function test_guest_order_confirmation_allows_only_orders_placed_in_the_current_session(): void
    {
        $customer = User::factory()->create(['email' => 'buyer@example.com', 'phone' => '9876543210', 'role' => 'customer']);
        $previousOrder = $customer->orders()->create([
            'order_number' => 'ORD-PREVIOUS', 'customer_name' => 'Previous Customer',
            'customer_email' => $customer->email, 'status' => 'processing',
        ]);
        $addressId = $this->prepareCheckout($this->product());
        $orderNumber = $this->placeOrder($addressId)->assertOk()->json('orderId');

        $this->get(route('order.success', $orderNumber))->assertOk();
        $this->get(route('order.success', $previousOrder->order_number))->assertRedirect(route('cart'));
        $this->flushSession();
        $this->get(route('order.success', $orderNumber))->assertRedirect(route('cart'));
        $this->assertSame(2, Order::where('user_id', $customer->id)->count());
        $this->assertGuest();
    }

    private function product(): Product
    {
        $category = Category::create(['slug' => 'checkout-customer-test', 'name' => 'Checkout Customer Test', 'is_active' => true]);

        return Product::create([
            'name' => 'Customer Checkout Product', 'slug' => 'customer-checkout-product', 'sku' => 'CHECKOUT-CUSTOMER-001',
            'category_id' => $category->id, 'jewellery_type' => 'Ring', 'metal_type' => 'Gold',
            'selling_price' => 1000, 'mrp' => 1200, 'gst_percentage' => 0, 'stock_quantity' => 10, 'is_active' => true,
        ]);
    }

    private function prepareCheckout(Product $product, array $overrides = []): string
    {
        $addressId = $this->postJson(route('checkout.addresses.store'), [
            'type' => 'Home', 'name' => 'Checkout Buyer', 'email' => 'buyer@example.com', 'phone' => '9876543210',
            'line1' => 'Buyer Street', 'city' => 'Kolkata', 'district' => 'Kolkata', 'state' => 'West Bengal',
            'pincode' => '700001', 'country' => 'India', ...$overrides,
        ])->assertOk()->json('address.id');
        $this->postJson(route('cart.store'), ['product_id' => $product->id, 'qty' => 2])->assertOk();

        return $addressId;
    }

    private function placeOrder(string $addressId): \Illuminate\Testing\TestResponse
    {
        return $this->postJson(route('checkout.order.store'), [
            'address_id' => $addressId, 'delivery' => 'standard', 'payment' => 'cod',
        ]);
    }
}

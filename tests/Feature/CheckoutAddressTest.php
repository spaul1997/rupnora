<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CheckoutAddressTest extends TestCase
{
    use DatabaseMigrations;

    public function test_checkout_requires_every_visible_address_field_except_landmark(): void
    {
        foreach (array_keys($this->address()) as $field) {
            $data = $this->address();
            unset($data[$field]);

            $this->postJson(route('checkout.addresses.store'), $data)
                ->assertUnprocessable()
                ->assertJsonValidationErrors($field);
        }

        $this->assertEmpty(session('checkout.guest_addresses', []));
    }

    public function test_checkout_rejects_invalid_address_values(): void
    {
        foreach (['email' => 'invalid-email', 'district' => str_repeat('x', 101), 'state' => 'Invalid State', 'pincode' => '12345'] as $field => $value) {
            $this->postJson(route('checkout.addresses.store'), [...$this->address(), $field => $value])
                ->assertUnprocessable()
                ->assertJsonValidationErrors($field);
        }

        $this->assertEmpty(session('checkout.guest_addresses', []));
    }

    public function test_guest_address_keeps_email_defaults_country_and_allows_optional_fields_to_be_omitted(): void
    {
        $this->postJson(route('checkout.addresses.store'), $this->address())
            ->assertOk()
            ->assertJsonPath('address.email', 'delivery@example.com')
            ->assertJsonPath('address.district', 'Kolkata District')
            ->assertJsonPath('address.country', 'India')
            ->assertJsonPath('address.line2', '')
            ->assertJsonPath('address.landmark', '');

        $this->get(route('checkout'))
            ->assertOk()
            ->assertViewHas('addresses', fn ($addresses) => $addresses[0]['email'] === 'delivery@example.com' && $addresses[0]['district'] === 'Kolkata District')
            ->assertSee('type="email"', false)
            ->assertSee('placeholder="District *"', false)
            ->assertDontSee('x-model.trim="newAddress.line2"', false)
            ->assertSee('<option value="West Bengal">West Bengal</option>', false)
            ->assertSee('type="hidden" name="country" value="India"', false)
            ->assertDontSee('placeholder="Country"', false);
    }

    public function test_customer_address_persists_email_and_keeps_country_as_india(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($customer)
            ->postJson(route('checkout.addresses.store'), [...$this->address(), 'country' => 'Another Country', 'landmark' => ''])
            ->assertOk()
            ->assertJsonPath('address.email', 'delivery@example.com')
            ->assertJsonPath('address.district', 'Kolkata District')
            ->assertJsonPath('address.country', 'India');

        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $customer->id,
            'email' => 'delivery@example.com',
            'district' => 'Kolkata District',
            'country' => 'India',
            'line2' => null,
            'landmark' => null,
        ]);

        $this->get(route('checkout'))
            ->assertOk()
            ->assertViewHas('addresses', fn ($addresses) => $addresses[0]['email'] === 'delivery@example.com' && $addresses[0]['district'] === 'Kolkata District');

        $this->get(route('account.addresses'))->assertOk()->assertSee('Kolkata District');
    }

    public function test_guest_can_edit_an_address_without_creating_a_duplicate_or_changing_its_default_status(): void
    {
        $original = $this->postJson(route('checkout.addresses.store'), [
            ...$this->address(), 'line2' => 'Existing apartment',
        ])->assertOk()->json('address');
        $other = $this->postJson(route('checkout.addresses.store'), [
            ...$this->address(), 'type' => 'Office',
        ])->assertOk()->json('address');

        $this->patchJson(route('checkout.addresses.update', $original['id']), [
            ...$this->address(),
            'name' => 'Updated Customer',
            'email' => 'updated@example.com',
            'district' => 'Updated District',
            'country' => 'Another Country',
            'default' => false,
        ])
            ->assertOk()
            ->assertJsonPath('address.id', $original['id'])
            ->assertJsonPath('address.name', 'Updated Customer')
            ->assertJsonPath('address.email', 'updated@example.com')
            ->assertJsonPath('address.district', 'Updated District')
            ->assertJsonPath('address.country', 'India')
            ->assertJsonPath('address.line2', 'Existing apartment')
            ->assertJsonPath('address.default', true)
            ->assertJsonCount(2, 'addresses')
            ->assertJsonPath('addresses.1', $other);

        $this->get(route('checkout'))
            ->assertOk()
            ->assertViewHas('addresses', fn ($addresses) => count($addresses) === 2
                && $addresses[0]['id'] === $original['id']
                && $addresses[0]['name'] === 'Updated Customer')
            ->assertSee('@click="editAddress(address.id)"', false);
    }

    public function test_customer_can_edit_the_same_saved_address_and_preserve_its_owner_and_default_status(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $address = $customer->addresses()->create([
            ...$this->address(),
            'country' => 'India',
            'is_default' => true,
            'line2' => 'Existing apartment',
        ]);

        $this->actingAs($customer)->patchJson(route('checkout.addresses.update', $address->id), [
            ...$this->address(),
            'name' => 'Updated Customer',
            'phone' => '9123456789',
            'line1' => 'Updated Street',
            'landmark' => 'Near the station',
            'country' => 'Another Country',
            'default' => false,
            'user_id' => 999,
            'is_default' => false,
        ])
            ->assertOk()
            ->assertJsonPath('address.id', $address->id)
            ->assertJsonPath('address.default', true)
            ->assertJsonCount(1, 'addresses');

        $this->assertDatabaseCount('customer_addresses', 1);
        $this->assertDatabaseHas('customer_addresses', [
            'id' => $address->id,
            'user_id' => $customer->id,
            'is_default' => true,
            'name' => 'Updated Customer',
            'phone' => '9123456789',
            'line1' => 'Updated Street',
            'line2' => 'Existing apartment',
            'landmark' => 'Near the station',
            'country' => 'India',
        ]);
        $this->get(route('checkout'))
            ->assertOk()
            ->assertViewHas('addresses', fn ($addresses) => $addresses[0]['name'] === 'Updated Customer');
    }

    public function test_guests_and_other_customers_cannot_edit_another_customers_address(): void
    {
        $owner = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $other = User::factory()->create(['role' => 'customer', 'is_active' => true]);
        $address = $owner->addresses()->create([...$this->address(), 'country' => 'India']);

        $this->patchJson(route('checkout.addresses.update', $address->id), $this->address())->assertNotFound();
        $this->actingAs($other)
            ->patchJson(route('checkout.addresses.update', $address->id), $this->address())
            ->assertNotFound();

        $this->assertDatabaseHas('customer_addresses', ['id' => $address->id, 'user_id' => $owner->id]);
        $this->assertDatabaseCount('customer_addresses', 1);
    }

    public function test_guest_updates_require_valid_fields_and_an_address_in_the_current_session(): void
    {
        $original = $this->postJson(route('checkout.addresses.store'), $this->address())
            ->assertOk()->json('address');
        $url = route('checkout.addresses.update', $original['id']);

        foreach (array_keys($this->address()) as $field) {
            $data = $this->address();
            unset($data[$field]);
            $this->patchJson($url, $data)->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        foreach (['email' => 'invalid-email', 'state' => 'Invalid State', 'pincode' => '12345'] as $field => $value) {
            $this->patchJson($url, [...$this->address(), $field => $value])
                ->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->assertSame([$original], session('checkout.guest_addresses'));

        $this->withSession(['checkout.guest_addresses' => []])
            ->patchJson($url, $this->address())->assertNotFound();
        $this->assertEmpty(session('checkout.guest_addresses'));
    }

    private function address(): array
    {
        return [
            'type' => 'Home',
            'name' => 'Checkout Customer',
            'email' => 'delivery@example.com',
            'phone' => '9876543210',
            'line1' => 'Test Street',
            'city' => 'Kolkata',
            'district' => 'Kolkata District',
            'state' => 'West Bengal',
            'pincode' => '700001',
        ];
    }
}

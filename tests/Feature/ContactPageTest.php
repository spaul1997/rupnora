<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_uses_active_managed_faqs_and_contact_images(): void
    {
        Faq::create([
            'question' => 'How quickly will support reply?',
            'answer' => 'Our team will reply as soon as possible.',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        Faq::create([
            'question' => 'Hidden support question',
            'answer' => 'This answer should not be shown.',
            'sort_order' => 2,
            'is_active' => false,
        ]);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Talk to us.')
            ->assertSee('We’re here to help.')
            ->assertSee('images/contact1.png')
            ->assertSee('images/contact2.png')
            ->assertSee('How quickly will support reply?')
            ->assertDontSee('Hidden support question');
    }

    public function test_guest_contact_request_is_saved_with_a_ticket_number(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'Ananya Rao',
            'email' => 'ananya@example.com',
            'phone' => '+91 98765 43210',
            'subject' => 'Product & styling advice',
            'message' => 'Please help me choose a necklace for an upcoming family celebration.',
        ]);

        $response
            ->assertRedirect(route('contact').'#contact-form')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Ananya Rao',
            'email' => 'ananya@example.com',
            'subject' => 'Product & styling advice',
            'status' => 'new',
            'user_id' => null,
        ]);

        $this->assertStringStartsWith('CNT-'.now()->year.'-', (string) ContactMessage::first()->ticket_no);
    }

    public function test_signed_in_customer_is_linked_to_their_contact_request(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->actingAs($customer)->post(route('contact.store'), [
            'name' => $customer->name,
            'email' => $customer->email,
            'subject' => 'Order support',
            'message' => 'I need an update about the expected delivery date for my recent order.',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'user_id' => $customer->id,
            'email' => $customer->email,
        ]);
    }

    public function test_contact_request_requires_a_meaningful_message(): void
    {
        $this->from(route('contact'))->post(route('contact.store'), [
            'name' => 'Ananya Rao',
            'email' => 'ananya@example.com',
            'subject' => 'Other',
            'message' => 'Too short',
        ])->assertRedirect(route('contact'))->assertSessionHasErrors('message');

        $this->assertDatabaseCount('contact_messages', 0);
    }
}

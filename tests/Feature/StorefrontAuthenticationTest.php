<?php

namespace Tests\Feature;

use App\Mail\ForgotPasswordOtpMail;
use App\Mail\PasswordResetSuccessMail;
use App\Mail\RegisterMail;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StorefrontAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_pages_are_available(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Create Account');
        $this->get(route('login'))->assertOk()->assertSee('Sign In');
        $this->get(route('password.request'))->assertOk()->assertSee('Forgot Password?');
    }

    public function test_customer_can_register_and_welcome_mail_bcc_uses_support_email(): void
    {
        Mail::fake();
        WebsiteSetting::current()->update(['support_email' => 'info@rupnora.in']);

        $response = $this->post(route('register.store'), [
            'first_name' => 'Ananya',
            'last_name' => 'Rao',
            'phone' => '+91 98765 43210',
            'email' => 'ananya@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('account.dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Ananya Rao',
            'email' => 'ananya@example.com',
            'role' => 'customer',
        ]);

        Mail::assertSent(RegisterMail::class, fn (RegisterMail $mail) => $mail->hasTo('ananya@example.com') && $mail->hasBcc('info@rupnora.in')
        );
    }

    public function test_customer_can_log_in_with_email_and_real_password(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => 'Secret123!',
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'customer@example.com',
            'password' => 'Secret123!',
        ]);

        $response->assertRedirect(route('account.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_can_log_in_with_equivalently_formatted_mobile_number(): void
    {
        $user = User::factory()->create([
            'phone' => '+91 98765 43210',
            'password' => 'Secret123!',
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'login' => '98765-43210',
            'password' => 'Secret123!',
        ]);

        $response->assertRedirect(route('account.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_can_reset_password_using_emailed_otp(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'phone' => '+91 98765 43210',
            'password' => 'OldPassword1!',
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->post(route('password.email'), [
            'login' => '9876543210',
        ])->assertRedirect(route('password.request'));

        $otp = null;
        Mail::assertSent(ForgotPasswordOtpMail::class, function (ForgotPasswordOtpMail $mail) use (&$otp, $user) {
            $otp = $mail->otp;

            return $mail->hasTo($user->email) && ! $mail->hasBcc('info@rupnora.in');
        });

        $this->post(route('password.verify'), [
            'otp' => str_split($otp),
        ])->assertRedirect(route('password.request'));

        $this->post(route('password.update'), [
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewPassword1!', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        Mail::assertSent(PasswordResetSuccessMail::class, fn (PasswordResetSuccessMail $mail) => $mail->hasTo($user->email)
        );
    }

    public function test_support_email_defaults_to_rupnora_address(): void
    {
        $this->assertSame('info@rupnora.in', WebsiteSetting::current()->support_email);
    }
}

<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordOtpMail;
use App\Mail\PasswordResetSuccessMail;
use App\Mail\RegisterMail;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthPageController extends Controller
{
    public function login(): View|RedirectResponse
    {
        if ($this->isStorefrontCustomer()) {
            return redirect()->route('account.dashboard');
        }

        return view('auth.login', ['title' => 'Sign In']);
    }

    public function loginStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $identifier = trim($data['login']);
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if ($field === 'email') {
            $identifier = mb_strtolower($identifier);
        }

        $credentials = [
            $field => $identifier,
            'password' => $data['password'],
            'role' => 'customer',
            'is_active' => true,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['login' => 'These credentials do not match an active customer account.'])
                ->onlyInput('login');
        }

        $request->session()->regenerate();
        $this->syncCustomerSession($request, Auth::user());

        return redirect()->intended(route('account.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been signed out.');
    }

    public function register(): View|RedirectResponse
    {
        if ($this->isStorefrontCustomer()) {
            return redirect()->route('account.dashboard');
        }

        return view('auth.register', ['title' => 'Create Account']);
    }

    public function registerStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'phone' => trim($data['phone']),
            'email' => mb_strtolower(trim($data['email'])),
            'password' => $data['password'],
            'role' => 'customer',
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $this->syncCustomerSession($request, $user);

        try {
            $pendingMail = Mail::to($user->email);
            $supportEmail = WebsiteSetting::current()->support_email;

            if ($supportEmail && strcasecmp($supportEmail, $user->email) !== 0) {
                $pendingMail->bcc($supportEmail);
            }

            $pendingMail->send(new RegisterMail($user));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('account.dashboard')
                ->with('warning', 'Your account was created, but the welcome email could not be sent.');
        }

        return redirect()
            ->route('account.dashboard')
            ->with('success', 'Welcome to Rupnora! Your account has been created.');
    }

    public function forgotPassword(Request $request): View
    {
        $reset = $request->session()->get('password_reset', []);

        return view('auth.forgot-password', [
            'title' => 'Forgot Password',
            'step' => $reset['step'] ?? 'request',
            'resetEmail' => $reset['email'] ?? null,
        ]);
    }

    public function sendResetCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:120'],
        ]);

        $identifier = trim($data['login']);
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $user = User::query()
            ->customers()
            ->where('is_active', true)
            ->where($field, $field === 'email' ? mb_strtolower($identifier) : $identifier)
            ->first();

        if (! $user) {
            return back()
                ->withErrors(['login' => 'We could not find an active account with those details.'])
                ->onlyInput('login');
        }

        $otp = (string) random_int(1000, 9999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($otp), 'created_at' => now()],
        );

        try {
            Mail::to($user->email)->send(new ForgotPasswordOtpMail($otp, $user->name));
        } catch (\Throwable $exception) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            report($exception);

            return back()
                ->withErrors(['login' => 'We could not send the reset code. Please try again shortly.'])
                ->onlyInput('login');
        }

        $request->session()->put('password_reset', [
            'email' => $user->email,
            'step' => 'otp',
        ]);

        return redirect()
            ->route('password.request')
            ->with('status', 'A 4-digit reset code was sent to '.$this->maskEmail($user->email).'.');
    }

    public function verifyResetCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'otp' => ['required', 'array', 'size:4'],
            'otp.*' => ['required', 'digits:1'],
        ]);

        $email = $request->session()->get('password_reset.email');
        $record = $email
            ? DB::table('password_reset_tokens')->where('email', $email)->first()
            : null;

        $otp = implode('', $data['otp']);
        $expired = ! $record
            || ! $record->created_at
            || Carbon::parse($record->created_at)->addMinutes(10)->isPast();

        if ($expired || ! Hash::check($otp, $record->token)) {
            return back()->withErrors(['otp' => 'The reset code is invalid or has expired.']);
        }

        $request->session()->put('password_reset.step', 'reset');
        $request->session()->put('password_reset.verified_at', now()->timestamp);

        return redirect()->route('password.request');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $reset = $request->session()->get('password_reset', []);
        $email = $reset['email'] ?? null;
        $verifiedAt = $reset['verified_at'] ?? null;

        if (! $email || ! $verifiedAt || now()->timestamp - (int) $verifiedAt > 600) {
            $request->session()->forget('password_reset');

            return redirect()
                ->route('password.request')
                ->withErrors(['login' => 'Your password reset session expired. Please request a new code.']);
        }

        $user = User::query()->customers()->where('email', $email)->first();

        if (! $user) {
            $request->session()->forget('password_reset');

            return redirect()->route('password.request');
        }

        $user->update(['password' => $data['password']]);
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $request->session()->forget('password_reset');

        try {
            Mail::to($user->email)->send(new PasswordResetSuccessMail($user->name));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()
            ->route('login')
            ->with('success', 'Your password has been updated. You can now sign in.');
    }

    private function isStorefrontCustomer(): bool
    {
        return Auth::check() && Auth::user()->role === 'customer';
    }

    private function syncCustomerSession(Request $request, User $user): void
    {
        $request->session()->put('storefront_customer', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email, 2);
        $visible = mb_substr($name, 0, min(2, mb_strlen($name)));

        return $visible.str_repeat('*', max(2, mb_strlen($name) - mb_strlen($visible))).'@'.$domain;
    }
}

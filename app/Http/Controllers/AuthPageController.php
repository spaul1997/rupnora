<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthPageController extends Controller
{
    public function login(Request $request)
    {
        if ($request->session()->has('storefront_customer')) {
            return redirect()->route('account.dashboard');
        }

        return view('auth.login', ['title' => 'Sign In']);
    }

    public function loginStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:120'],
        ]);

        $request->session()->put('storefront_customer', [
            'name' => 'Ananya Rao',
            'email' => str_contains($data['login'], '@') ? $data['login'] : 'ananya.rao@example.com',
        ]);

        return redirect()->intended(route('account.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('storefront_customer');

        return redirect()->route('login')->with('success', 'You have been signed out.');
    }

    public function register()
    {
        return view('auth.register', ['title' => 'Create Account']);
    }

    public function forgotPassword()
    {
        return view('auth.forgot-password', ['title' => 'Forgot Password']);
    }
}

<?php

namespace App\Http\Controllers;

class AuthPageController extends Controller
{
    public function login()
    {
        return view('auth.login', ['title' => 'Sign In']);
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

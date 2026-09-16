<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStorefrontCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('storefront_customer')) {
            return redirect()
                ->guest(route('login'))
                ->with('error', 'Please sign in to access your account.');
        }

        return $next($request);
    }
}

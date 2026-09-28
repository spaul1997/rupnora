<?php

namespace App\Http\Middleware;

use App\Services\AffiliateAttributionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureAffiliateReferral
{
    public function __construct(private readonly AffiliateAttributionService $attribution) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->query->has('ref') && ! $request->is('admin/*') && ! $request->user()?->isAdmin()) {
            $this->attribution->capture($request);
        }

        return $next($request);
    }
}

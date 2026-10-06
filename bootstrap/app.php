<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [\App\Http\Middleware\CaptureAffiliateReferral::class]);

        $middleware->redirectUsersTo(function (Request $request): string {
            $user = $request->user();

            if ($request->is('admin/*') && $user?->isAdmin() && $user->is_active) {
                return route('admin.dashboard');
            }

            return route('home');
        });

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'storefront.customer' => \App\Http\Middleware\EnsureStorefrontCustomer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            $message = 'The upload is larger than the server allows. Product images are allowed up to 5MB each, but the full request must also be below the server post_max_size limit.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return response()->view('errors.upload-too-large', [
                'message' => $message,
                'postMaxSize' => ini_get('post_max_size') ?: 'unknown',
                'uploadMaxFilesize' => ini_get('upload_max_filesize') ?: 'unknown',
            ], 413);
        });
    })->create();

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Sign In — Rupnora Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('favicon.png') }}" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:500,600,700|inter:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-scope flex min-h-screen items-center justify-center bg-charcoal px-4 font-sans antialiased">

    <div class="w-full max-w-sm">
        <div class="mb-8 text-center">
            <img src="{{ asset('logo-w.png') }}" alt="Rupnora" class="mx-auto h-16 w-auto object-contain">
            <p class="mt-1 text-xs uppercase tracking-[0.3em] text-champagne-light">Admin Panel</p>
        </div>

        <div class="rounded-2xl border border-ivory/10 bg-ivory/5 p-8">
            <h1 class="text-lg font-semibold text-ivory text-center">Sign in to your account</h1>
            <p class="mt-1 text-sm text-ivory/50 text-center">Enter your admin credentials to continue.</p>

            @if ($errors->any())
                <div class="mt-5 rounded-lg bg-error/10 px-4 py-3 text-sm text-red-300">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-xs font-medium text-ivory/70">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus class="w-full rounded-lg border border-ivory/15 bg-ivory/5 px-3.5 py-2.5 text-sm text-ivory placeholder:text-ivory/30 focus:border-champagne-light focus:outline-none focus:ring-2 focus:ring-champagne/25" placeholder="Enter your Email ID">
                </div>
                <div x-data="{ showPassword: false }">
                    <label for="password" class="mb-1.5 block text-xs font-medium text-ivory/70">Password</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required class="w-full rounded-lg border border-ivory/15 bg-ivory/5 px-3.5 py-2.5 pr-11 text-sm text-ivory placeholder:text-ivory/30 focus:border-champagne-light focus:outline-none focus:ring-2 focus:ring-champagne/25" placeholder="Enter your Password">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-ivory/45 transition-colors hover:text-ivory" :aria-label="showPassword ? 'Hide password' : 'Show password'">
                            <svg x-show="!showPassword" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg x-show="showPassword" x-cloak class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M3 3l18 18" stroke-linecap="round" />
                                <path d="M10.6 10.6a2 2 0 002.8 2.8" />
                                <path d="M7.4 7.5C4.3 9 2.5 12 2.5 12s3.5 6 9.5 6c1.5 0 2.8-.4 4-1" />
                                <path d="M12.8 6.1C18.3 6.6 21.5 12 21.5 12a16.4 16.4 0 01-2.2 2.8" />
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full rounded-lg bg-champagne-dark py-3 text-sm font-semibold text-charcoal transition-colors hover:bg-champagne-light">
                    Sign In
                </button>
            </form>
        </div>
    </div>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Sign In — Aurelle Admin</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:500,600,700|inter:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-scope flex min-h-screen items-center justify-center bg-charcoal px-4 font-sans antialiased">

    <div class="w-full max-w-sm">
        <div class="mb-8 text-center">
            <span class="font-display text-3xl text-ivory">Aurelle</span>
            <p class="mt-1 text-xs uppercase tracking-[0.3em] text-champagne-light">Admin Panel</p>
        </div>

        <div class="rounded-2xl border border-ivory/10 bg-ivory/5 p-8">
            <h1 class="text-lg font-semibold text-ivory">Sign in to your account</h1>
            <p class="mt-1 text-sm text-ivory/50">Enter your admin credentials to continue.</p>

            @if ($errors->any())
                <div class="mt-5 rounded-lg bg-error/10 px-4 py-3 text-sm text-red-300">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-xs font-medium text-ivory/70">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus class="w-full rounded-lg border border-ivory/15 bg-ivory/5 px-3.5 py-2.5 text-sm text-ivory placeholder:text-ivory/30 focus:border-champagne-light focus:outline-none focus:ring-2 focus:ring-champagne/25" placeholder="admin@aurellejewellery.com">
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-xs font-medium text-ivory/70">Password</label>
                    <input type="password" name="password" id="password" required class="w-full rounded-lg border border-ivory/15 bg-ivory/5 px-3.5 py-2.5 text-sm text-ivory placeholder:text-ivory/30 focus:border-champagne-light focus:outline-none focus:ring-2 focus:ring-champagne/25" placeholder="Enter your password">
                </div>
                <label class="flex items-center gap-2 text-sm text-ivory/60">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-ivory/20 bg-transparent text-champagne-dark focus:ring-champagne-dark/40">
                    Remember me
                </label>
                <button type="submit" class="w-full rounded-lg bg-champagne-dark py-3 text-sm font-semibold text-charcoal transition-colors hover:bg-champagne-light">
                    Sign In
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-ivory/30">Demo credentials: admin@aurellejewellery.com / password</p>
    </div>

</body>
</html>

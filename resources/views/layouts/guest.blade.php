@php
    $isRegister = request()->routeIs('register');

    $authHeading = match (true) {
        request()->routeIs('login') => __('Sign in'),
        request()->routeIs('register') => __('Create a student account'),
        request()->routeIs('password.request') => __('Reset your password'),
        request()->routeIs('password.reset') => __('Choose a new password'),
        request()->routeIs('password.confirm') => __('Confirm your password'),
        request()->routeIs('verification.notice') => __('Verify your email'),
        default => __('Campus Resource Sharing'),
    };

    $authDescription = match (true) {
        request()->routeIs('login') => __('Use the email address and password registered with your account.'),
        request()->routeIs('register') => __('For active students. Your name and registration number are checked against college records.'),
        request()->routeIs('password.request') => __('Enter your email address and we will send a reset link if an account exists.'),
        request()->routeIs('password.reset') => __('Choose a new password for your account.'),
        request()->routeIs('password.confirm') => __('Confirm your password before continuing.'),
        request()->routeIs('verification.notice') => __('Open the verification link sent to your email address.'),
        default => __('Sign in to manage resources, requests, and notifications.'),
    };

    $authAsideLabel = match (true) {
        request()->routeIs('login') => __('Student access'),
        request()->routeIs('register') => __('Student registration'),
        default => __('Account access'),
    };

    $authAsideTitle = match (true) {
        request()->routeIs('login') => __('Borrow equipment and academic materials from other students.'),
        request()->routeIs('register') => __('Use the details already on file with the college.'),
        request()->routeIs('password.request', 'password.reset') => __('Recover access to your resource account.'),
        request()->routeIs('password.confirm') => __('Your account is protected.'),
        request()->routeIs('verification.notice') => __('One more step to activate your account.'),
        default => __('Campus Resource Sharing'),
    };

    $authAsideText = match (true) {
        request()->routeIs('login') => __('Search the shared catalog, send a request to the owner, and keep track of pickup and return from your account.'),
        request()->routeIs('register') => __('Your account is created after your name and registration number are checked against the active student registry.'),
        request()->routeIs('password.request') => __('Enter the email registered with your account and we will send a reset link if it matches.'),
        request()->routeIs('password.reset') => __('Choose a new password for your account. You will use it the next time you sign in.'),
        request()->routeIs('password.confirm') => __('Confirm your password before continuing to protected account settings.'),
        request()->routeIs('verification.notice') => __('Open the verification link sent to your email address to continue.'),
        default => __('Sign in to manage resources, requests, and notifications.'),
    };

    $authAsidePoints = match (true) {
        request()->routeIs('login') => [
            __('Use the email address registered with your account.'),
            __('Send a request and wait for the owner to approve it.'),
            __('Track pickup and return dates in your account.'),
        ],
        request()->routeIs('register') => [
            __('Use your full name as it appears in college records.'),
            __('Enter an active registration number in the required format.'),
            __('Use an email address you check regularly.'),
        ],
        request()->routeIs('password.request') => [
            __('Use the email address registered with your account.'),
            __('The reset link is time-limited.'),
        ],
        request()->routeIs('password.reset') => [
            __('Use at least eight characters.'),
            __('Choose a password you have not used elsewhere.'),
        ],
        default => [],
    };

    $authMaxWidth = $isRegister ? 'max-w-[560px]' : 'max-w-[420px]';
    $authGrid = $isRegister
        ? 'lg:grid-cols-[minmax(0,1fr)_560px]'
        : 'lg:grid-cols-[minmax(0,1fr)_420px]';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0b1424">

        <title>{{ $authHeading }} | {{ __('Campus Resource Sharing') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|manrope:500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-brand-700 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
            {{ __('Skip to content') }}
        </a>

        <div class="flex min-h-screen flex-col bg-slate-100">
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex h-[68px] w-full max-w-[1180px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <a href="{{ route('public.home') }}" class="flex min-w-0 items-center gap-2.5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-700 text-white">
                            <x-icon name="book-open" class="h-5 w-5" />
                        </span>

                        <span class="min-w-0">
                            <span class="block truncate font-display text-[13px] font-bold leading-tight text-slate-900">{{ __('Campus Resource Sharing') }}</span>
                            <span class="mt-0.5 block truncate text-[10px] font-medium uppercase leading-tight tracking-[0.1em] text-slate-500">{{ __('Gomendra Multiple College') }}</span>
                        </span>
                    </a>

                    <a href="{{ route('public.home') }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                        <x-icon name="arrow-left" class="h-3.5 w-3.5" />
                        {{ __('Browse resources') }}
                    </a>
                </div>
            </header>

            <main id="main-content" class="flex flex-1 items-start px-4 py-10 sm:px-6 lg:items-center lg:px-8 lg:py-14">
                <div class="mx-auto grid w-full max-w-[1180px] items-start gap-10 lg:gap-16 {{ $authGrid }}">
                    <section class="hidden pt-1 lg:block" aria-labelledby="auth-aside-title">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">{{ $authAsideLabel }}</p>

                        <h2 id="auth-aside-title" class="mt-3 max-w-md font-display text-[30px] font-bold leading-[1.2] text-slate-900">
                            {{ $authAsideTitle }}
                        </h2>

                        <p class="mt-4 max-w-md text-sm leading-6 text-slate-600">{{ $authAsideText }}</p>

                        @if ($authAsidePoints)
                            <ul class="mt-6 max-w-md space-y-3 border-t border-slate-300 pt-5 text-sm leading-6 text-slate-600">
                                @foreach ($authAsidePoints as $point)
                                    <li class="flex gap-3">
                                        <span class="mt-2.5 h-1 w-1 shrink-0 rounded-full bg-slate-400"></span>
                                        <span>{{ $point }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                    </section>

                    <section class="mx-auto w-full {{ $authMaxWidth }} lg:mx-0" aria-labelledby="auth-heading">
                        <div class="mb-6">
                            <h1 id="auth-heading" class="font-display text-[28px] font-bold leading-tight tracking-tight text-slate-900">{{ $authHeading }}</h1>
                            <p class="mt-2 max-w-md text-sm leading-6 text-slate-600">{{ $authDescription }}</p>
                        </div>

                        <div class="rounded-lg border border-slate-300 bg-white p-5 sm:p-7">
                            {{ $slot }}
                        </div>
                    </section>
                </div>
            </main>

            <footer class="border-t border-slate-200 bg-white">
                <div class="mx-auto flex w-full max-w-[1180px] flex-col gap-1 px-4 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <p>{{ __('Gomendra Multiple College') }} &middot; {{ __('Campus Resource Sharing') }}</p>
                    <p>{{ __('Student portal') }}</p>
                </div>
            </footer>
        </div>
    </body>
</html>

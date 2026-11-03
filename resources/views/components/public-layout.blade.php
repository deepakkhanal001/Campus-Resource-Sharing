@props(['title' => null])

@php
    $currentUser = $publicUser ?? null;
    $unreadCount = $publicUnreadCount ?? 0;
    $userInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($currentUser?->name ?? 'U', 0, 1));
    $isAdmin = $currentUser && strtolower((string) $currentUser->role) === 'admin';

    $navigation = [
        ['label' => __('Browse'), 'route' => 'public.resources.index', 'active' => request()->routeIs('public.resources.*', 'public.borrowings.request', 'public.borrowings.store')],
        ['label' => __('Categories'), 'route' => 'public.categories.index', 'active' => request()->routeIs('public.categories.*')],
    ];

    if ($currentUser) {
        $navigation[] = ['label' => __('My requests'), 'route' => 'public.borrowings.index', 'active' => request()->routeIs('public.borrowings.index')];
        $navigation[] = ['label' => __('Incoming'), 'route' => 'public.owner-requests.index', 'active' => request()->routeIs('public.owner-requests.*')];
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0f172a">

        <title>{{ $title ? $title.' | ' : '' }}{{ __('Campus Resource Sharing') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|manrope:500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-brand-600 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
            {{ __('Skip to content') }}
        </a>

        <div
            x-data="{
                mobileOpen: false,
                confirmOpen: false,
                confirmTitle: 'Confirm action',
                confirmMessage: '',
                confirmLabel: 'Continue',
                pendingFormId: null,
                askConfirmation(form) {
                    if (! form.id) {
                        form.id = 'public-confirm-form-' + Date.now() + '-' + Math.random().toString(16).slice(2);
                    }

                    this.pendingFormId = form.id;
                    this.confirmTitle = form.dataset.confirmTitle || 'Confirm action';
                    this.confirmMessage = form.dataset.confirm || 'Are you sure you want to continue?';
                    this.confirmLabel = form.dataset.confirmLabel || 'Continue';
                    this.confirmOpen = true;
                    this.$nextTick(() => this.$refs.confirmButton?.focus());
                },
                closeConfirmation() {
                    this.confirmOpen = false;
                    this.pendingFormId = null;
                },
                submitConfirmed() {
                    const form = document.getElementById(this.pendingFormId);

                    this.confirmOpen = false;
                    this.pendingFormId = null;

                    if (form) {
                        form.submit();
                    }
                },
                handleSubmit(event) {
                    const form = event.target;

                    if (! (form instanceof HTMLFormElement) || ! form.dataset.confirm) {
                        return;
                    }

                    event.preventDefault();
                    this.askConfirmation(form);
                }
            }"
            x-effect="document.body.classList.toggle('overflow-hidden', mobileOpen || confirmOpen)"
            @keydown.escape.window="mobileOpen = false; closeConfirmation()"
            @submit="handleSubmit($event)"
            class="public-shell flex min-h-screen flex-col bg-slate-50"
        >
            <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
                <nav class="mx-auto flex h-[68px] max-w-[1440px] items-center gap-3 px-4 sm:px-6 lg:px-8" aria-label="{{ __('Primary navigation') }}">
                    <a href="{{ route('public.home') }}" class="flex shrink-0 items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-700 text-white shadow-sm">
                            <x-icon name="book-open" class="h-5 w-5" />
                        </span>

                        <span class="hidden sm:block">
                            <span class="block font-display text-[13px] font-bold leading-tight text-slate-900">{{ __('Campus Resources') }}</span>
                            <span class="block text-[10px] font-medium uppercase leading-tight tracking-[0.1em] text-slate-500">{{ __('Gomendra Multiple College') }}</span>
                        </span>
                    </a>

                    <div class="ms-4 hidden items-center gap-1 lg:flex">
                        @foreach ($navigation as $link)
                            <a
                                href="{{ route($link['route']) }}"
                                @class([
                                    'rounded-lg px-3 py-2 text-sm font-medium transition',
                                    'bg-brand-50 text-brand-700' => $link['active'],
                                    'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $link['active'],
                                ])
                            >
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </div>

                    <div class="ms-auto flex items-center gap-2">
                        @auth
                            <a
                                href="{{ route('public.notifications.index') }}"
                                class="relative inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                                aria-label="{{ trans_choice('{0} No unread notifications|{1} :count unread notification|[2,*] :count unread notifications', $unreadCount, ['count' => $unreadCount]) }}"
                            >
                                <x-icon name="bell" class="h-5 w-5" />

                                @if ($unreadCount > 0)
                                    <span class="absolute -end-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-rose-600 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white ring-2 ring-white">
                                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                    </span>
                                @endif
                            </a>

                            <div class="hidden lg:block">
                                <x-dropdown align="right" width="56">
                                    <x-slot name="trigger">
                                        <button
                                            type="button"
                                            class="flex items-center gap-2 rounded-full border border-transparent p-1 text-left transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                                            aria-label="{{ __('Open account menu') }}"
                                        >
                                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-700 text-sm font-semibold text-white">
                                                {{ $userInitial }}
                                            </span>
                                            <x-icon name="chevron-down" class="h-4 w-4 text-slate-400" />
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <div class="border-b border-slate-100 px-4 py-3">
                                            <p class="truncate text-sm font-semibold text-slate-900">{{ $currentUser?->name }}</p>
                                            <p class="truncate text-xs text-slate-500">{{ $currentUser?->email }}</p>
                                        </div>

                                        <x-dropdown-link :href="route('public.notifications.index')">
                                            {{ __('Notifications') }}
                                        </x-dropdown-link>

                                        <x-dropdown-link :href="route('dashboard')">
                                            {{ $isAdmin ? __('Admin dashboard') : __('Dashboard') }}
                                        </x-dropdown-link>

                                        <div class="border-t border-slate-100">
                                            <x-dropdown-link :href="route('profile.edit')">
                                                {{ __('Profile settings') }}
                                            </x-dropdown-link>

                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <x-dropdown-link
                                                    :href="route('logout')"
                                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                                >
                                                    {{ __('Log out') }}
                                                </x-dropdown-link>
                                            </form>
                                        </div>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        @else
                            <div class="hidden items-center gap-2 lg:flex">
                                <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 hover:text-slate-900">
                                    {{ __('Sign in') }}
                                </a>
                                <a href="{{ route('register') }}" class="crs-btn crs-btn-primary">
                                    {{ __('Create account') }}
                                </a>
                            </div>
                        @endauth

                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 lg:hidden"
                            @click="mobileOpen = true"
                            :aria-expanded="mobileOpen"
                            aria-controls="public-mobile-menu"
                            aria-label="{{ __('Open menu') }}"
                        >
                            <x-icon name="menu" class="h-5 w-5" />
                        </button>
                    </div>
                </nav>
            </header>

            <div
                x-cloak
                x-show="mobileOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 lg:hidden"
                role="dialog"
                aria-modal="true"
                aria-label="{{ __('Navigation menu') }}"
            >
                <div class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm" @click="mobileOpen = false"></div>

                <div
                    id="public-mobile-menu"
                    x-show="mobileOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="-translate-x-full"
                    class="relative flex h-full w-full max-w-xs flex-col bg-white shadow-2xl"
                >
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4">
                        <a href="{{ route('public.home') }}" class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-700 text-white">
                                <x-icon name="book-open" class="h-5 w-5" />
                            </span>
                            <span class="font-display text-sm font-bold text-slate-900">{{ __('Campus Resources') }}</span>
                        </a>

                        <button type="button" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900" @click="mobileOpen = false" aria-label="{{ __('Close menu') }}">
                            <x-icon name="x-mark" class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto px-3 py-4">
                        <div class="space-y-1">
                            @foreach ($navigation as $link)
                                <a href="{{ route($link['route']) }}" @class(['flex items-center rounded-lg px-3 py-2.5 text-sm font-medium', 'bg-brand-50 text-brand-700' => $link['active'], 'text-slate-700 hover:bg-slate-100' => ! $link['active']])>
                                    {{ $link['label'] }}
                                </a>
                            @endforeach

                            @auth
                                <a href="{{ route('public.notifications.index') }}" @class(['flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium', 'bg-brand-50 text-brand-700' => request()->routeIs('public.notifications.*'), 'text-slate-700 hover:bg-slate-100' => ! request()->routeIs('public.notifications.*')])>
                                    <span>{{ __('Notifications') }}</span>
                                    @if ($unreadCount > 0)
                                        <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-rose-600 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                                    @endif
                                </a>
                            @endauth
                        </div>

                        <div class="mt-6 border-t border-slate-200 pt-5">
                            @auth
                                <div class="px-3">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $currentUser?->name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $currentUser?->email }}</p>
                                </div>

                                <div class="mt-3 space-y-1">
                                    <a href="{{ route('dashboard') }}" class="flex rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">{{ $isAdmin ? __('Admin dashboard') : __('Dashboard') }}</a>
                                    <a href="{{ route('profile.edit') }}" class="flex rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100">{{ __('Profile settings') }}</a>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full rounded-lg px-3 py-2.5 text-left text-sm font-medium text-rose-600 hover:bg-rose-50">{{ __('Log out') }}</button>
                                    </form>
                                </div>
                            @else
                                <div class="space-y-2 px-1">
                                    <a href="{{ route('login') }}" class="crs-btn crs-btn-secondary w-full">{{ __('Sign in') }}</a>
                                    <a href="{{ route('register') }}" class="crs-btn crs-btn-primary w-full">{{ __('Create account') }}</a>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

            <div class="mx-auto w-full max-w-[1440px] px-4 pt-4 sm:px-6 lg:px-8">
                @include('partials.flash')
            </div>

            <main id="main-content" class="flex-1">
                {{ $slot }}
            </main>

            <footer class="mt-12 border-t border-slate-200 bg-white">
                <div class="mx-auto flex max-w-[1440px] flex-col gap-5 px-4 py-7 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-700 text-white">
                            <x-icon name="book-open" class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ __('Campus Resource Sharing') }}</p>
                            <p class="text-xs text-slate-500">{{ __('A shared library for the campus community.') }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-500">
                        <a href="{{ route('public.resources.index') }}" class="transition hover:text-slate-900">{{ __('Browse resources') }}</a>
                        <a href="{{ route('public.categories.index') }}" class="transition hover:text-slate-900">{{ __('Categories') }}</a>
                        <span>&copy; {{ now()->year }} {{ __('Campus Resources') }}</span>
                    </div>
                </div>
            </footer>

            <div
                x-cloak
                x-show="confirmOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[70] overflow-y-auto"
                role="dialog"
                aria-modal="true"
                aria-labelledby="public-confirm-title"
                aria-describedby="public-confirm-description"
            >
                <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="closeConfirmation()"></div>

                <div class="flex min-h-full items-end justify-center p-4 sm:items-center">
                    <div
                        x-show="confirmOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-2xl"
                        @click.stop
                    >
                        <div class="flex items-start gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600 ring-1 ring-inset ring-rose-100">
                                <x-icon name="exclamation-triangle" class="h-5 w-5" />
                            </span>

                            <div class="min-w-0">
                                <h2 id="public-confirm-title" class="text-base font-semibold text-slate-900" x-text="confirmTitle"></h2>
                                <p id="public-confirm-description" class="mt-1 text-sm leading-6 text-slate-600" x-text="confirmMessage"></p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <x-secondary-button type="button" @click="closeConfirmation()">
                                {{ __('Go back') }}
                            </x-secondary-button>

                            <button
                                type="button"
                                x-ref="confirmButton"
                                @click="submitConfirmed()"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2 active:bg-rose-800"
                            >
                                <span x-text="confirmLabel"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

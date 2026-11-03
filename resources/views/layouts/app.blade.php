<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0f172a">

        <title>{{ __('Campus Resource Sharing') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|manrope:500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @php
            $currentUser = auth()->user();
            $currentRole = $currentUser?->getRoleNames()->first() ?? $currentUser?->role ?? 'member';
            $userInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($currentUser?->name ?? 'U', 0, 1));
        @endphp

        <div
            x-data="{
                sidebarOpen: false,
                collapsed: false,
                confirmOpen: false,
                confirmTitle: 'Confirm action',
                confirmMessage: '',
                confirmLabel: 'Delete',
                pendingFormId: null,
                init() {
                    this.collapsed = localStorage.getItem('crs-sidebar-collapsed') === '1';
                },
                toggleSidebar() {
                    this.collapsed = ! this.collapsed;
                    localStorage.setItem('crs-sidebar-collapsed', this.collapsed ? '1' : '0');
                },
                askConfirmation(form) {
                    if (! form.id) {
                        form.id = 'confirm-form-' + Date.now() + '-' + Math.random().toString(16).slice(2);
                    }

                    this.pendingFormId = form.id;
                    this.confirmTitle = form.dataset.confirmTitle || 'Confirm action';
                    this.confirmMessage = form.dataset.confirm || 'Are you sure you want to continue?';
                    this.confirmLabel = form.dataset.confirmLabel || 'Delete';
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
            x-effect="document.body.classList.toggle('overflow-hidden', sidebarOpen || confirmOpen)"
            @keydown.escape.window="sidebarOpen = false; closeConfirmation()"
            @submit="handleSubmit($event)"
            class="min-h-screen"
        >
            @include('layouts.navigation')

            <div class="min-h-screen transition-[padding] duration-200 ease-out md:pl-20 lg:pl-[17rem]" :class="collapsed ? 'lg:!pl-24' : ''">
                <header class="relative z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur lg:sticky lg:top-0">
                    <div class="mx-auto flex max-w-[1500px] items-start gap-3 px-4 py-3 sm:px-6 lg:px-8">
                        <button
                            type="button"
                            class="mt-1 inline-flex shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 md:hidden"
                            @click="sidebarOpen = true"
                            :aria-expanded="sidebarOpen"
                            aria-controls="app-sidebar"
                            aria-label="{{ __('Open navigation') }}"
                        >
                            <x-icon name="menu" class="h-5 w-5" />
                        </button>

                        <div class="min-w-0 flex-1">
                            @isset($header)
                                {{ $header }}
                            @else
                                <h1 class="text-lg font-semibold tracking-tight text-slate-900">
                                    {{ __('Campus Resource Sharing') }}
                                </h1>
                            @endisset
                        </div>

                        <div class="shrink-0">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button
                                        type="button"
                                        class="flex items-center gap-2 rounded-full border border-transparent p-1 text-left transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                                        aria-haspopup="menu"
                                        aria-label="{{ __('Open account menu') }}"
                                    >
                                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-700 text-sm font-semibold text-white">
                                            {{ $userInitial }}
                                        </span>

                                        <span class="hidden min-w-0 md:block">
                                            <span class="block max-w-[10rem] truncate text-sm font-semibold text-slate-800">{{ $currentUser?->name }}</span>
                                            <span class="block text-xs text-slate-500">{{ ucfirst($currentRole) }}</span>
                                        </span>

                                        <x-icon name="chevron-down" class="hidden h-4 w-4 text-slate-400 md:block" />
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="border-b border-slate-100 px-4 py-3">
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ $currentUser?->name }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $currentUser?->email }}</p>
                                    </div>

                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
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
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </header>

                <main class="min-w-0">
                    {{ $slot }}
                </main>
            </div>

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
                aria-labelledby="confirm-dialog-title"
                aria-describedby="confirm-dialog-description"
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
                                <h2 id="confirm-dialog-title" class="text-base font-semibold text-slate-900" x-text="confirmTitle"></h2>
                                <p id="confirm-dialog-description" class="mt-1 text-sm leading-6 text-slate-600" x-text="confirmMessage"></p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <x-secondary-button type="button" @click="closeConfirmation()">
                                {{ __('Cancel') }}
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

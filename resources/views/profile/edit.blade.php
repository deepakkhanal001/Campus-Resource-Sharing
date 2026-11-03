@php
    $roleLabel = $user->getRoleNames()->first() ?? $user->role ?? 'member';
    $userInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1));
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Profile')"
            :description="__('Manage your account information, password, and account status.')"
        />
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-[1440px] space-y-6 px-4 sm:px-6 lg:px-8 2xl:max-w-screen-2xl">
            @include('partials.flash')

            <div class="grid gap-6 lg:grid-cols-3">
                <aside class="space-y-6">
                    <section class="crs-card p-5 sm:p-6" aria-label="{{ __('Account summary') }}">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-brand-600 text-xl font-semibold text-white">
                                {{ $userInitial }}
                            </span>

                            <div class="min-w-0">
                                <p class="truncate text-base font-semibold text-slate-900">{{ $user->name }}</p>
                                <p class="truncate text-sm text-slate-500">{{ $user->email }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <x-status-badge :status="$roleLabel" />
                            <x-status-badge :status="$user->status ?? 'active'" />
                        </div>

                        <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">{{ __('Email verified') }}</dt>
                                <dd class="font-medium text-slate-900">{{ $user->hasVerifiedEmail() ? __('Yes') : __('No') }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">{{ __('Member since') }}</dt>
                                <dd class="text-right font-medium text-slate-900">{{ $user->created_at?->format('M Y') ?: '—' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="crs-card p-5 sm:p-6" aria-labelledby="profile-tip-title">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                <x-icon name="shield-check" class="h-4 w-4" />
                            </span>

                            <div>
                                <h2 id="profile-tip-title" class="text-sm font-semibold text-slate-900">{{ __('Keep your account secure') }}</h2>
                                <p class="mt-1 text-sm leading-6 text-slate-500">{{ __('Use a unique password and never share your sign-in details with anyone.') }}</p>
                            </div>
                        </div>
                    </section>
                </aside>

                <div class="space-y-6 lg:col-span-2">
                    <div class="crs-card p-5 sm:p-6">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    <div class="crs-card p-5 sm:p-6">
                        @include('profile.partials.update-password-form')
                    </div>

                    <div class="crs-card border-rose-200 p-5 sm:p-6">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

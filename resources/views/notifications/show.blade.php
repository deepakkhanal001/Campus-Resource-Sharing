@php
    $isAdmin = auth()->user()->hasRole('admin');
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Notification')"
            :description="__('Message details and delivery status.')"
        >
            <x-slot name="actions">
                <x-button-link :href="route('notifications.index')" variant="secondary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    {{ __('Back') }}
                </x-button-link>

                @if ($isAdmin)
                    <x-button-link :href="route('notifications.edit', $notification)">
                        <x-icon name="pencil" class="h-4 w-4" />
                        {{ __('Edit') }}
                    </x-button-link>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <div class="grid gap-6 lg:grid-cols-3">
                <section class="crs-card lg:col-span-2" aria-labelledby="notification-message-title">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4 sm:px-6">
                        <div>
                            <h2 id="notification-message-title" class="text-base font-semibold text-slate-900">{{ __('Message') }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ ucfirst($notification->type) }}</p>
                        </div>

                        <x-status-badge :status="$notification->is_read ? 'read' : 'unread'" class="shrink-0" />
                    </div>

                    <div class="flex items-start gap-4 p-5 sm:p-6">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600 ring-1 ring-inset ring-brand-100">
                            <x-icon name="bell" class="h-5 w-5" />
                        </span>

                        <p class="min-w-0 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $notification->message }}</p>
                    </div>
                </section>

                <aside class="space-y-6">
                    <section class="crs-card" aria-labelledby="notification-details-title">
                        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                            <h2 id="notification-details-title" class="text-base font-semibold text-slate-900">{{ __('Details') }}</h2>
                        </div>

                        <dl class="divide-y divide-slate-100">
                            <div class="flex items-center justify-between gap-4 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-slate-500">{{ __('Recipient') }}</dt>
                                <dd class="text-right text-sm font-medium text-slate-900">{{ $notification->user?->name ?: '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-slate-500">{{ __('Type') }}</dt>
                                <dd class="text-right text-sm font-medium text-slate-900">{{ ucfirst($notification->type) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-slate-500">{{ __('Created') }}</dt>
                                <dd class="text-right text-sm font-medium text-slate-900">{{ $notification->created_at?->format('d M Y, h:i A') ?: '—' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="crs-card p-5 sm:p-6" aria-labelledby="notification-actions-title">
                        <h2 id="notification-actions-title" class="text-base font-semibold text-slate-900">{{ __('Actions') }}</h2>

                        <div class="mt-4 space-y-2">
                            @if (! $notification->is_read)
                                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                    @csrf
                                    @method('PATCH')

                                    <x-secondary-button class="w-full">
                                        <x-icon name="check" class="h-4 w-4" />
                                        {{ __('Mark as read') }}
                                    </x-secondary-button>
                                </form>
                            @endif

                            @if ($isAdmin)
                                <form method="POST" action="{{ route('notifications.destroy', $notification) }}" data-confirm="This notification will be permanently deleted." data-confirm-title="Delete notification?" data-confirm-label="Delete notification">
                                    @csrf
                                    @method('DELETE')

                                    <x-danger-button class="w-full">
                                        <x-icon name="trash" class="h-4 w-4" />
                                        {{ __('Delete notification') }}
                                    </x-danger-button>
                                </form>
                            @endif
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>

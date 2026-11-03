@php
    $isAdmin = auth()->user()->hasRole('admin');
    $hasFilters = request()->filled('q') || request()->filled('read');
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Notifications')"
            :description="__('Review updates, requests, and announcements sent to users.')"
        >
            @if ($isAdmin)
                <x-slot name="actions">
                    <x-button-link :href="route('notifications.create')">
                        <x-icon name="plus" class="h-4 w-4" />
                        {{ __('New notification') }}
                    </x-button-link>
                </x-slot>
            @endif
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-[1440px] space-y-4 px-4 sm:px-6 lg:px-8 2xl:max-w-screen-2xl">
            @include('partials.flash')

            <form method="GET" action="{{ route('notifications.index') }}" class="crs-card p-4 sm:p-5" role="search">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-12">
                    <div class="lg:col-span-7">
                        <label for="notification-search" class="crs-label">{{ __('Search') }}</label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input
                                id="notification-search"
                                name="q"
                                type="search"
                                value="{{ request('q') }}"
                                placeholder="{{ __('Message, type, or recipient') }}"
                                class="crs-input ps-9"
                            >
                        </div>
                    </div>

                    <div class="lg:col-span-3">
                        <label for="notification-read" class="crs-label">{{ __('Read status') }}</label>
                        <select id="notification-read" name="read" class="crs-select mt-1.5">
                            <option value="">{{ __('All notifications') }}</option>
                            <option value="unread" @selected(request('read') === 'unread')>{{ __('Unread only') }}</option>
                            <option value="read" @selected(request('read') === 'read')>{{ __('Read only') }}</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2 lg:col-span-2">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 active:bg-brand-800">
                            <x-icon name="search" class="h-4 w-4" />
                            {{ __('Filter') }}
                        </button>

                        @if ($hasFilters)
                            <x-button-link :href="route('notifications.index')" variant="secondary">
                                {{ __('Reset') }}
                            </x-button-link>
                        @endif
                    </div>
                </div>
            </form>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">
                    {{ trans_choice(':count notification|:count notifications', $notifications->total(), ['count' => $notifications->total()]) }}
                </p>
            </div>

            @if ($notifications->isEmpty())
                <div class="crs-card">
                    <x-empty-state
                        icon="bell"
                        :title="$hasFilters ? __('No matching notifications') : __('No notifications yet')"
                        :description="$hasFilters ? __('Try adjusting your search or filters.') : __('Borrowing updates and announcements will appear here.')"
                    >
                        <x-slot name="action">
                            @if ($hasFilters)
                                <x-button-link :href="route('notifications.index')" variant="secondary">
                                    {{ __('Clear filters') }}
                                </x-button-link>
                            @elseif ($isAdmin)
                                <x-button-link :href="route('notifications.create')">
                                    <x-icon name="plus" class="h-4 w-4" />
                                    {{ __('Create notification') }}
                                </x-button-link>
                            @endif
                        </x-slot>
                    </x-empty-state>
                </div>
            @else
                <div class="crs-table-wrap">
                    <table class="crs-table">
                        <thead class="crs-thead">
                            <tr>
                                @if ($isAdmin)
                                    <th scope="col" class="crs-th">{{ __('User') }}</th>
                                @endif
                                <th scope="col" class="crs-th">{{ __('Message') }}</th>
                                <th scope="col" class="crs-th hidden lg:table-cell">{{ __('Type') }}</th>
                                <th scope="col" class="crs-th">{{ __('Read') }}</th>
                                <th scope="col" class="crs-th hidden md:table-cell">{{ __('Created') }}</th>
                                <th scope="col" class="crs-th text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($notifications as $notification)
                                <tr class="{{ $notification->is_read ? '' : 'bg-brand-50/40' }} transition hover:bg-slate-50/70">
                                    @if ($isAdmin)
                                        <td class="crs-td">
                                            <div class="flex items-center gap-2">
                                                @if (! $notification->is_read)
                                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500" aria-label="{{ __('Unread') }}"></span>
                                                @endif
                                                <span class="font-medium text-slate-900">{{ $notification->user?->name ?: '—' }}</span>
                                            </div>
                                        </td>
                                    @endif

                                    <td class="crs-td">
                                        <a href="{{ route('notifications.show', $notification) }}" class="font-medium text-slate-900 transition hover:text-brand-600">
                                            {{ \Illuminate\Support\Str::limit($notification->message, 100) }}
                                        </a>
                                        <p class="mt-0.5 text-xs text-slate-500 lg:hidden">
                                            {{ ucfirst($notification->type) }}
                                            <span class="mx-1 text-slate-300">·</span>
                                            {{ $notification->created_at?->format('d M Y, h:i A') ?: '—' }}
                                        </p>
                                    </td>

                                    <td class="crs-td hidden lg:table-cell">{{ ucfirst($notification->type) }}</td>
                                    <td class="crs-td"><x-status-badge :status="$notification->is_read ? 'read' : 'unread'" /></td>
                                    <td class="crs-td hidden whitespace-nowrap md:table-cell">{{ $notification->created_at?->format('d M Y, h:i A') ?: '—' }}</td>

                                    <td class="crs-td whitespace-nowrap text-right">
                                        <a href="{{ route('notifications.show', $notification) }}" class="crs-link">{{ __('View') }}</a>

                                        @if (! $notification->is_read)
                                            <form method="POST" action="{{ route('notifications.read', $notification) }}" class="ms-3 inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="crs-link">{{ __('Mark read') }}</button>
                                            </form>
                                        @endif

                                        @if ($isAdmin)
                                            <a href="{{ route('notifications.edit', $notification) }}" class="crs-link ms-3">{{ __('Edit') }}</a>

                                            <form method="POST" action="{{ route('notifications.destroy', $notification) }}" class="ms-3 inline" data-confirm="This notification will be permanently deleted." data-confirm-title="Delete notification?" data-confirm-label="Delete notification">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="crs-danger-link">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="$isAdmin ? __('Dashboard') : __('My dashboard')"
            :description="$today.' · '.($isAdmin ? __('Platform overview and activity that needs attention.') : __('Your resources, requests, and notifications at a glance.'))"
        >
            <x-slot name="actions">
                @if ($isAdmin)
                    <x-button-link :href="route('resources.create')">
                        <x-icon name="plus" class="h-4 w-4" />
                        {{ __('Add resource') }}
                    </x-button-link>
                @else
                    <x-button-link :href="route('resources.index')" variant="secondary">
                        {{ __('Browse resources') }}
                    </x-button-link>
                    <x-button-link :href="route('resources.create')">
                        <x-icon name="plus" class="h-4 w-4" />
                        {{ __('Share resource') }}
                    </x-button-link>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    @php
        $quickActions = $isAdmin
            ? [
                ['label' => __('Add resource'), 'description' => __('List a new item'), 'icon' => 'plus', 'href' => route('resources.create')],
                ['label' => __('Add category'), 'description' => __('Organize listings'), 'icon' => 'folder', 'href' => route('categories.create')],
                ['label' => __('Upload file'), 'description' => __('Attach a digital file'), 'icon' => 'upload', 'href' => route('digital-files.create')],
                ['label' => __('Send notification'), 'description' => __('Message a user'), 'icon' => 'send', 'href' => route('notifications.create')],
                ['label' => __('Review requests'), 'description' => __('View pending items'), 'icon' => 'clock', 'href' => route('borrowing-records.index', ['status' => 'pending'])],
            ]
            : [
                ['label' => __('Share resource'), 'description' => __('List an item'), 'icon' => 'plus', 'href' => route('resources.create')],
                ['label' => __('Upload file'), 'description' => __('Attach a file'), 'icon' => 'upload', 'href' => route('digital-files.create')],
                ['label' => __('Request to borrow'), 'description' => __('Find an item'), 'icon' => 'borrowing', 'href' => route('public.resources.index', ['type' => 'physical', 'availability' => 'available'])],
                ['label' => __('My requests'), 'description' => __('Track your borrowing'), 'icon' => 'clock', 'href' => route('public.borrowings.index')],
                ['label' => __('Incoming requests'), 'description' => __('Review requests for your items'), 'icon' => 'inbox', 'href' => route('public.owner-requests.index')],
                ['label' => __('Browse resources'), 'description' => __('Explore the library'), 'icon' => 'collection', 'href' => route('public.resources.index')],
            ];
    @endphp

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-[1440px] space-y-6 px-4 sm:px-6 lg:px-8 2xl:max-w-screen-2xl">
            @include('partials.flash')

            <section aria-label="{{ __('Platform summary') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                @foreach ($stats as $stat)
                    <x-stat-card
                        :label="$stat['label']"
                        :value="$stat['value']"
                        :icon="$stat['icon']"
                        :tone="$stat['tone']"
                        :href="$stat['href']"
                        :hint="$stat['hint']"
                    />
                @endforeach
            </section>

            <div class="grid gap-6 xl:grid-cols-3">
                <div class="space-y-6 xl:col-span-2">
                    <section class="crs-card" aria-labelledby="recent-borrowings-title">
                        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div>
                                <h2 id="recent-borrowings-title" class="text-base font-semibold text-slate-900">{{ __('Recent borrowing requests') }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ __($isAdmin ? 'Latest requests across the platform.' : 'Your latest borrowing activity.') }}</p>
                            </div>

                            <a href="{{ route('borrowing-records.index') }}" class="crs-link shrink-0 text-sm">{{ __('View all') }}</a>
                        </div>

                        @if ($recentBorrowings->isEmpty())
                            <x-empty-state
                                icon="borrowing"
                                :title="__('No borrowing requests yet')"
                                :description="__($isAdmin ? 'New requests will appear here when students begin borrowing resources.' : 'Requests you make or receive will appear here.')"
                            >
                                <x-slot name="action">
                                    <x-button-link :href="route('public.resources.index', ['type' => 'physical', 'availability' => 'available'])" variant="secondary">
                                        {{ __('Find a resource to request') }}
                                    </x-button-link>
                                </x-slot>
                            </x-empty-state>
                        @else
                            <ul role="list" class="divide-y divide-slate-100">
                                @foreach ($recentBorrowings as $record)
                                    <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                        <div class="flex min-w-0 items-start gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">
                                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($record->requester_name ?? 'U', 0, 1)) }}
                                            </span>

                                            <div class="min-w-0">
                                                <a href="{{ route('borrowing-records.show', $record->id) }}" class="break-words font-semibold text-slate-900 transition hover:text-brand-600">
                                                    {{ $record->resource_title ?? __('Resource unavailable') }}
                                                </a>

                                                <p class="mt-0.5 text-xs leading-5 text-slate-500">
                                                    {{ __('Requested by :name', ['name' => $record->requester_name ?? __('Unknown user')]) }}

                                                    @if ($record->return_date)
                                                        <span class="mx-1 text-slate-300">·</span>
                                                        {{ __('Return by :date', ['date' => \Carbon\Carbon::parse($record->return_date)->format('d M Y')]) }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex shrink-0 items-center gap-2 self-start sm:self-auto">
                                            @if ($record->is_overdue)
                                                <x-status-badge status="overdue" />
                                            @else
                                                <x-status-badge :status="$record->status" />
                                            @endif

                                            <a
                                                href="{{ route('borrowing-records.show', $record->id) }}"
                                                class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
                                                aria-label="{{ __('View borrowing request') }}"
                                            >
                                                <x-icon name="arrow-right" class="h-4 w-4" />
                                            </a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>

                    <section class="crs-card" aria-labelledby="recent-resources-title">
                        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div>
                                <h2 id="recent-resources-title" class="text-base font-semibold text-slate-900">{{ __('Recently listed resources') }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ __($isAdmin ? 'Newest additions to the shared library.' : 'Available resources recently added by others.') }}</p>
                            </div>

                            <a href="{{ route('resources.index') }}" class="crs-link shrink-0 text-sm">{{ __('View all') }}</a>
                        </div>

                        @if ($recentResources->isEmpty())
                            <x-empty-state
                                icon="collection"
                                :title="__('No resources listed yet')"
                                :description="__('New resource listings will appear here once they are added.')"
                            >
                                <x-slot name="action">
                                    <x-button-link :href="route('resources.create')" variant="secondary">
                                        {{ __('Add the first resource') }}
                                    </x-button-link>
                                </x-slot>
                            </x-empty-state>
                        @else
                            <ul role="list" class="divide-y divide-slate-100">
                                @foreach ($recentResources as $resource)
                                    <li class="flex items-center gap-3 px-5 py-4 sm:px-6">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                            <x-icon :name="$resource->type === 'digital' ? 'document' : 'collection'" class="h-5 w-5" />
                                        </span>

                                        <div class="min-w-0 flex-1">
                                            <a href="{{ route('resources.show', $resource->id) }}" class="break-words font-semibold text-slate-900 transition hover:text-brand-600">
                                                {{ $resource->title }}
                                            </a>
                                            <p class="mt-0.5 text-xs leading-5 text-slate-500">
                                                {{ ucfirst($resource->type) }}
                                                <span class="mx-1 text-slate-300">·</span>
                                                {{ $resource->category_name ?? __('Uncategorised') }}
                                                <span class="mx-1 text-slate-300">·</span>
                                                {{ $resource->owner_name ?? __('Unknown owner') }}
                                            </p>
                                        </div>

                                        <x-status-badge :status="$resource->availability_status" class="shrink-0" />
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </div>

                <div class="space-y-6">
                    <section class="crs-card" aria-labelledby="quick-actions-title">
                        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                            <h2 id="quick-actions-title" class="text-base font-semibold text-slate-900">{{ __('Quick actions') }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Common tasks for your account.') }}</p>
                        </div>

                        <div class="grid grid-cols-1 gap-2 p-4 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                            @foreach ($quickActions as $action)
                                <a
                                    href="{{ $action['href'] }}"
                                    class="group flex min-w-0 items-start gap-3 rounded-lg border border-slate-200 p-3 transition hover:border-brand-200 hover:bg-brand-50/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                                >
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-white group-hover:text-brand-600">
                                        <x-icon :name="$action['icon']" class="h-4 w-4" />
                                    </span>

                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold text-slate-800 group-hover:text-brand-700">{{ $action['label'] }}</span>
                                        <span class="mt-0.5 block truncate text-xs text-slate-500">{{ $action['description'] }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </section>

                    @if ($isAdmin)
                        <section class="crs-card" aria-labelledby="recent-users-title">
                            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                                <h2 id="recent-users-title" class="text-base font-semibold text-slate-900">{{ __('Recent registrations') }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ __('Newest accounts on the platform.') }}</p>
                            </div>

                            @if ($recentUsers->isEmpty())
                                <x-empty-state
                                    icon="users"
                                    :title="__('No users registered yet')"
                                    :description="__('New account registrations will appear here.')"
                                />
                            @else
                                <ul role="list" class="divide-y divide-slate-100">
                                    @foreach ($recentUsers as $user)
                                        <li class="flex items-center gap-3 px-5 py-3.5 sm:px-6">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-semibold text-brand-600">
                                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}
                                            </span>

                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                                                <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                                            </div>

                                            <x-status-badge :status="$user->role" class="shrink-0" />
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @else
                        <section class="crs-card" aria-labelledby="recent-notifications-title">
                            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                <div>
                                    <h2 id="recent-notifications-title" class="text-base font-semibold text-slate-900">{{ __('Recent notifications') }}</h2>
                                    <p class="mt-1 text-sm text-slate-500">{{ __('Updates sent to your account.') }}</p>
                                </div>

                                <a href="{{ route('notifications.index') }}" class="crs-link shrink-0 text-sm">{{ __('View all') }}</a>
                            </div>

                            @if ($recentNotifications->isEmpty())
                                <x-empty-state
                                    icon="bell"
                                    :title="__('No notifications yet')"
                                    :description="__('Borrowing updates and announcements will appear here.')"
                                />
                            @else
                                <ul role="list" class="divide-y divide-slate-100">
                                    @foreach ($recentNotifications as $notification)
                                        <li class="px-5 py-4 sm:px-6">
                                            <div class="flex items-start gap-3">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                                    <x-icon name="bell" class="h-4 w-4" />
                                                </span>

                                                <div class="min-w-0 flex-1">
                                                    <a href="{{ route('notifications.show', $notification->id) }}" class="break-words text-sm font-medium text-slate-800 transition hover:text-brand-600">
                                                        {{ \Illuminate\Support\Str::limit($notification->message, 90) }}
                                                    </a>
                                                    <p class="mt-1 text-xs text-slate-500">
                                                        {{ ucfirst($notification->type) }}
                                                        <span class="mx-1 text-slate-300">·</span>
                                                        {{ \Carbon\Carbon::parse($notification->created_at)->format('d M Y, h:i A') }}
                                                    </p>
                                                </div>

                                                <x-status-badge :status="$notification->is_read ? 'read' : 'unread'" class="shrink-0" />
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

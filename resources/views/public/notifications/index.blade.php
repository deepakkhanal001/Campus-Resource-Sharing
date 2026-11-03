@php
    $iconForType = function (string $type): array {
        return match ($type) {
            'borrow_request' => ['icon' => 'inbox', 'classes' => 'bg-amber-50 text-amber-600 ring-amber-100'],
            'borrow_approved' => ['icon' => 'check-circle', 'classes' => 'bg-emerald-50 text-emerald-600 ring-emerald-100'],
            'borrow_rejected' => ['icon' => 'x-mark', 'classes' => 'bg-rose-50 text-rose-600 ring-rose-100'],
            'borrow_picked_up' => ['icon' => 'borrowing', 'classes' => 'bg-sky-50 text-sky-600 ring-sky-100'],
            'borrow_returned' => ['icon' => 'arrow-path', 'classes' => 'bg-emerald-50 text-emerald-600 ring-emerald-100'],
            'borrow_cancelled' => ['icon' => 'x-mark', 'classes' => 'bg-slate-100 text-slate-500 ring-slate-200'],
            default => ['icon' => 'bell', 'classes' => 'bg-brand-50 text-brand-600 ring-brand-100'],
        };
    };
@endphp

<x-public-layout :title="__('Notifications')">
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-4xl px-4 py-7 sm:px-6 lg:px-8 lg:py-9">
            <nav class="flex items-center gap-1.5 text-xs text-slate-500" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('public.home') }}" class="transition hover:text-slate-900">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <span class="font-medium text-slate-700">{{ __('Notifications') }}</span>
            </nav>

            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="font-display text-[30px] font-bold tracking-tight text-slate-900 sm:text-[36px]">{{ __('Notifications') }}</h1>
                    <p class="mt-2 text-[13px] leading-5 text-slate-500">{{ __('Updates about your borrowing requests, approvals, pickups, and returns.') }}</p>
                </div>

                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('public.notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <x-icon name="check" class="h-4 w-4" />
                            {{ __('Mark all as read') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex gap-2">
                <a
                    href="{{ route('public.notifications.index') }}"
                    @class([
                        'inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-medium transition',
                        'bg-brand-600 text-white' => $filter === 'all',
                        'bg-white text-slate-600 ring-1 ring-inset ring-slate-200 hover:bg-slate-50 hover:text-slate-900' => $filter !== 'all',
                    ])
                >
                    {{ __('All') }}
                    <span @class(['rounded-full px-1.5 py-0.5 text-[10px] font-bold', 'bg-white/15 text-white' => $filter === 'all', 'bg-slate-100 text-slate-500' => $filter !== 'all'])>{{ $totalCount }}</span>
                </a>

                <a
                    href="{{ route('public.notifications.index', ['filter' => 'unread']) }}"
                    @class([
                        'inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-medium transition',
                        'bg-brand-600 text-white' => $filter === 'unread',
                        'bg-white text-slate-600 ring-1 ring-inset ring-slate-200 hover:bg-slate-50 hover:text-slate-900' => $filter !== 'unread',
                    ])
                >
                    {{ __('Unread') }}
                    <span @class(['rounded-full px-1.5 py-0.5 text-[10px] font-bold', 'bg-white/15 text-white' => $filter === 'unread', 'bg-rose-100 text-rose-600' => $filter !== 'unread' && $unreadCount > 0, 'bg-slate-100 text-slate-500' => $filter !== 'unread' && $unreadCount === 0])>{{ $unreadCount }}</span>
                </a>
            </div>

            <p class="text-sm text-slate-500">
                {{ trans_choice(':count notification|:count notifications', $notifications->total(), ['count' => $notifications->total()]) }}
            </p>
        </div>

        @if ($notifications->isEmpty())
            <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                    <x-icon name="bell" class="h-6 w-6" />
                </span>
                <h2 class="mt-4 text-sm font-semibold text-slate-900">{{ $filter === 'unread' ? __('No unread notifications') : __('No notifications yet') }}</h2>
                <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">
                    {{ $filter === 'unread' ? __('You are all caught up. New updates will appear here.') : __('Borrowing updates and owner responses will appear here.') }}
                </p>
            </div>
        @else
            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <ul class="divide-y divide-slate-100">
                    @foreach ($notifications as $notification)
                        @php
                            $visual = $iconForType((string) $notification->type);
                            $isUnread = ! (bool) $notification->is_read;
                            $createdAt = $notification->created_at
                                ? \Illuminate\Support\Carbon::parse($notification->created_at)->diffForHumans()
                                : null;
                        @endphp

                        <li class="relative flex gap-4 px-5 py-5 sm:px-6 {{ $isUnread ? 'bg-brand-50/40' : '' }}">
                            @if ($isUnread)
                                <span class="absolute inset-y-0 start-0 w-0.5 bg-brand-500" aria-hidden="true"></span>
                            @endif

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1 ring-inset {{ $visual['classes'] }}">
                                <x-icon :name="$visual['icon']" class="h-5 w-5" />
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                    <div class="min-w-0">
                                        <p class="text-sm leading-6 {{ $isUnread ? 'font-semibold text-slate-900' : 'font-medium text-slate-700' }}">
                                            {{ $notification->message }}
                                        </p>

                                        <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                            @if ($createdAt)
                                                <span>{{ $createdAt }}</span>
                                            @endif
                                            @if ($isUnread)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-brand-100 px-2 py-0.5 text-[10px] font-semibold text-brand-700">
                                                    <span class="h-1 w-1 rounded-full bg-brand-500" aria-hidden="true"></span>
                                                    {{ __('Unread') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($isUnread)
                                        <form method="POST" action="{{ route('public.notifications.read', $notification->id) }}" class="shrink-0">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                                                <x-icon name="check" class="h-3.5 w-3.5" />
                                                {{ __('Mark read') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-public-layout>


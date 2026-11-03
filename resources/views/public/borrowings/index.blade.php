@php
    $statusLabels = [
        '' => __('All'),
        'pending' => __('Requested'),
        'approved' => __('Approved'),
        'picked_up' => __('Taken'),
        'returned' => __('Returned'),
        'rejected' => __('Rejected'),
        'cancelled' => __('Cancelled'),
    ];
    $totalCount = array_sum($counts);
@endphp

<x-public-layout :title="__('My requests')">
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-[1440px] px-4 py-7 sm:px-6 lg:px-8 lg:py-9">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <nav class="flex items-center gap-1.5 text-xs text-slate-500" aria-label="{{ __('Breadcrumb') }}">
                        <a href="{{ route('public.home') }}" class="transition hover:text-slate-900">{{ __('Home') }}</a>
                        <span aria-hidden="true">/</span>
                        <span class="font-medium text-slate-700">{{ __('My requests') }}</span>
                    </nav>

                    <h1 class="mt-4 font-display text-[30px] font-bold tracking-tight text-slate-900 sm:text-[36px]">{{ __('My requests') }}</h1>
                    <p class="mt-2 max-w-2xl text-[13px] leading-5 text-slate-500">{{ __('Track every borrowing request you have sent, from owner review through pickup and return.') }}</p>
                </div>

                <a href="{{ route('public.resources.index') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                    <x-icon name="search" class="h-4 w-4" />
                    {{ __('Find a resource') }}
                </a>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        <div class="grid min-w-0 gap-6 lg:grid-cols-[16rem_minmax(0,1fr)] lg:items-start">
            <aside class="min-w-0 space-y-4 lg:sticky lg:top-24">
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h2 class="text-sm font-semibold text-slate-900">{{ __('Request summary') }}</h2>
                    </div>

                    <dl class="divide-y divide-slate-100">
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-sm text-slate-500">{{ __('All requests') }}</dt>
                            <dd class="text-sm font-semibold text-slate-900">{{ $totalCount }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-sm text-slate-500">{{ __('Awaiting approval') }}</dt>
                            <dd class="text-sm font-semibold text-amber-600">{{ $counts['pending'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-sm text-slate-500">{{ __('Active loans') }}</dt>
                            <dd class="text-sm font-semibold text-sky-600">{{ $counts['approved'] + $counts['picked_up'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-sm text-slate-500">{{ __('Completed') }}</dt>
                            <dd class="text-sm font-semibold text-emerald-600">{{ $counts['returned'] }}</dd>
                        </div>
                    </dl>
                </section>

                <form method="GET" action="{{ route('public.borrowings.index') }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm" role="search">
                    @if ($filters['status'])
                        <input type="hidden" name="status" value="{{ $filters['status'] }}">
                    @endif

                    <label for="request-search" class="crs-label">{{ __('Search requests') }}</label>
                    <div class="relative mt-1.5">
                        <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                            <x-icon name="search" class="h-4 w-4" />
                        </span>
                        <input
                            id="request-search"
                            name="q"
                            type="search"
                            value="{{ $filters['q'] }}"
                            placeholder="{{ __('Resource or owner') }}"
                            class="crs-input ps-9"
                        >
                    </div>

                    <button type="submit" class="mt-3 inline-flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        {{ __('Search') }}
                    </button>
                </form>
            </aside>

            <section class="min-w-0" aria-labelledby="my-requests-list-title">
                <h2 id="my-requests-list-title" class="sr-only">{{ __('Request list') }}</h2>

                <div class="flex gap-2 overflow-x-auto pb-2">
                    @foreach ($statusLabels as $statusValue => $statusLabel)
                        @php
                            $statusQuery = array_filter([
                                'status' => $statusValue ?: null,
                                'q' => $filters['q'] ?: null,
                            ]);
                            $isActive = $filters['status'] === $statusValue;
                            $statusCount = $statusValue === '' ? $totalCount : $counts[$statusValue];
                        @endphp

                        <a
                            href="{{ route('public.borrowings.index', $statusQuery) }}"
                            @class([
                                'inline-flex shrink-0 items-center gap-2 rounded-full px-3.5 py-2 text-sm font-medium transition',
                                'bg-brand-600 text-white' => $isActive,
                                'bg-white text-slate-600 ring-1 ring-inset ring-slate-200 hover:bg-slate-50 hover:text-slate-900' => ! $isActive,
                            ])
                        >
                            {{ $statusLabel }}
                            <span @class(['rounded-full px-1.5 py-0.5 text-[10px] font-bold', 'bg-white/15 text-white' => $isActive, 'bg-slate-100 text-slate-500' => ! $isActive])>
                                {{ $statusCount }}
                            </span>
                        </a>
                    @endforeach
                </div>

                <div class="mt-4 flex items-center justify-between gap-3">
                    <p class="text-sm text-slate-500">
                        {{ trans_choice(':count request|:count requests', $requests->total(), ['count' => $requests->total()]) }}
                    </p>

                    @if ($hasFilters)
                        <a href="{{ route('public.borrowings.index') }}" class="text-sm font-semibold text-brand-600 transition hover:text-brand-800">{{ __('Reset filters') }}</a>
                    @endif
                </div>

                @if ($requests->isEmpty())
                    <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <x-icon name="borrowing" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-4 text-sm font-semibold text-slate-900">{{ $hasFilters ? __('No matching requests') : __('You have not requested anything yet') }}</h3>
                        <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">
                            {{ $hasFilters ? __('Try a different search or status filter.') : __('When you request a physical resource, it will appear here with its latest status.') }}
                        </p>

                        @if ($hasFilters)
                            <a href="{{ route('public.borrowings.index') }}" class="mt-5 inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                {{ __('Clear filters') }}
                            </a>
                        @else
                            <a href="{{ route('public.resources.index', ['type' => 'physical', 'availability' => 'available']) }}" class="mt-5 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                                {{ __('Browse physical resources') }}
                            </a>
                        @endif
                    </div>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($requests as $item)
                            @php
                                $isOverdue = in_array($item->status, ['approved', 'picked_up'], true)
                                    && $item->return_date
                                    && \Illuminate\Support\Carbon::parse($item->return_date)->lt(today());
                                $requestedAt = $item->created_at
                                    ? \Illuminate\Support\Carbon::parse($item->created_at)->format('d M Y')
                                    : null;
                            @endphp

                            <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if ($isOverdue)
                                                <x-status-badge status="overdue" />
                                            @else
                                                <x-borrowing-status :status="$item->status" />
                                            @endif

                                            <span class="text-xs text-slate-500">#{{ $item->id }}</span>
                                            @if ($requestedAt)
                                                <span class="text-xs text-slate-400">{{ __('Requested :date', ['date' => $requestedAt]) }}</span>
                                            @endif
                                        </div>

                                        <h3 class="mt-3 text-base font-semibold text-slate-900">
                                            @if ($item->resource_deleted_at)
                                                <span class="text-slate-500">{{ $item->resource_title }} ({{ __('unavailable') }})</span>
                                            @else
                                                <a href="{{ route('public.resources.show', $item->resource_id) }}" class="transition hover:text-brand-700">{{ $item->resource_title }}</a>
                                            @endif
                                        </h3>

                                        <p class="mt-1 text-sm text-slate-500">{{ __('Owner: :name', ['name' => $item->owner_name]) }}</p>

                                        <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500">
                                            <span class="inline-flex items-center gap-1.5">
                                                <x-icon name="calendar" class="h-3.5 w-3.5 text-slate-400" />
                                                {{ __('Pickup: :date', ['date' => $item->pickup_date ? \Illuminate\Support\Carbon::parse($item->pickup_date)->format('d M Y') : __('Not set')]) }}
                                            </span>
                                            <span class="inline-flex items-center gap-1.5 {{ $isOverdue ? 'font-medium text-rose-600' : '' }}">
                                                <x-icon name="clock" class="h-3.5 w-3.5" />
                                                {{ __('Return: :date', ['date' => $item->return_date ? \Illuminate\Support\Carbon::parse($item->return_date)->format('d M Y') : __('Not set')]) }}
                                            </span>
                                        </div>

                                        @if ($item->request_message)
                                            <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3">
                                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Your message') }}</p>
                                                <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $item->request_message }}</p>
                                            </div>
                                        @endif

                                        @if ($item->status === 'rejected' && $item->rejection_reason)
                                            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
                                                <p class="text-xs font-semibold uppercase tracking-wider text-rose-700">{{ __('Rejection remarks') }}</p>
                                                <p class="mt-1 whitespace-pre-line text-sm leading-6 text-rose-700">{{ $item->rejection_reason }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex shrink-0 flex-row gap-2 sm:flex-col">
                                        <a href="{{ route('public.resources.show', $item->resource_id) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                            {{ __('View resource') }}
                                        </a>

                                        @if ($item->status === 'pending')
                                            <form method="POST" action="{{ route('public.borrowings.cancel', $item->id) }}" data-confirm="This will cancel your pending borrowing request. The owner will be notified." data-confirm-title="Cancel request?" data-confirm-label="Cancel request">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">
                                                    {{ __('Cancel request') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                @if ($isOverdue)
                                    <div class="flex items-center gap-2 border-t border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-700 sm:px-6">
                                        <x-icon name="exclamation-triangle" class="h-4 w-4 shrink-0" />
                                        {{ __('This loan is past its expected return date. Contact the owner to arrange the return.') }}
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
                        {{ $requests->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-public-layout>


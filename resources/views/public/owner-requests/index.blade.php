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
    $allCount = array_sum($counts);
@endphp

<x-public-layout :title="__('Incoming requests')">
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-[1440px] px-4 py-7 sm:px-6 lg:px-8 lg:py-9">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <nav class="flex items-center gap-1.5 text-xs text-slate-500" aria-label="{{ __('Breadcrumb') }}">
                        <a href="{{ route('public.home') }}" class="transition hover:text-slate-900">{{ __('Home') }}</a>
                        <span aria-hidden="true">/</span>
                        <span class="font-medium text-slate-700">{{ __('Incoming requests') }}</span>
                    </nav>

                    <h1 class="mt-4 font-display text-[30px] font-bold tracking-tight text-slate-900 sm:text-[36px]">{{ __('Incoming requests') }}</h1>
                    <p class="mt-2 max-w-2xl text-[13px] leading-5 text-slate-500">{{ __('Review requests for your resources, confirm pickups, and mark items as returned when they come back.') }}</p>
                </div>

                <a href="{{ route('public.borrowings.index') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    <x-icon name="borrowing" class="h-4 w-4" />
                    {{ __('My requests') }}
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
                            <dd class="text-sm font-semibold text-slate-900">{{ $allCount }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-sm text-slate-500">{{ __('Awaiting review') }}</dt>
                            <dd class="text-sm font-semibold text-amber-600">{{ $counts['pending'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-sm text-slate-500">{{ __('Active loans') }}</dt>
                            <dd class="text-sm font-semibold text-sky-600">{{ $counts['approved'] + $counts['picked_up'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                            <dt class="text-sm text-slate-500">{{ __('Returned') }}</dt>
                            <dd class="text-sm font-semibold text-emerald-600">{{ $counts['returned'] }}</dd>
                        </div>
                    </dl>
                </section>

                <form method="GET" action="{{ route('public.owner-requests.index') }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm" role="search">
                    @if ($filters['status'])
                        <input type="hidden" name="status" value="{{ $filters['status'] }}">
                    @endif

                    <div>
                        <label for="incoming-search" class="crs-label">{{ __('Search requests') }}</label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input
                                id="incoming-search"
                                name="q"
                                type="search"
                                value="{{ $filters['q'] }}"
                                placeholder="{{ __('Resource or requester') }}"
                                class="crs-input ps-9"
                            >
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="incoming-resource" class="crs-label">{{ __('Resource') }}</label>
                        <select id="incoming-resource" name="resource_id" class="crs-select mt-1.5">
                            <option value="">{{ __('All resources') }}</option>
                            @foreach ($myResources as $myResource)
                                <option value="{{ $myResource->id }}" @selected((int) $filters['resource_id'] === (int) $myResource->id)>{{ $myResource->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="mt-3 inline-flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        {{ __('Apply') }}
                    </button>
                </form>
            </aside>

            <section class="min-w-0" aria-labelledby="incoming-requests-list-title">
                <h2 id="incoming-requests-list-title" class="sr-only">{{ __('Incoming request list') }}</h2>

                <div class="flex gap-2 overflow-x-auto pb-2">
                    @foreach ($statusLabels as $statusValue => $statusLabel)
                        @php
                            $statusQuery = array_filter([
                                'status' => $statusValue ?: null,
                                'q' => $filters['q'] ?: null,
                                'resource_id' => $filters['resource_id'] ?: null,
                            ]);
                            $isActive = $filters['status'] === $statusValue;
                            $statusCount = $statusValue === '' ? $allCount : $counts[$statusValue];
                        @endphp

                        <a
                            href="{{ route('public.owner-requests.index', $statusQuery) }}"
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
                        <a href="{{ route('public.owner-requests.index') }}" class="text-sm font-semibold text-brand-600 transition hover:text-brand-800">{{ __('Reset filters') }}</a>
                    @endif
                </div>

                @if ($requests->isEmpty())
                    <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <x-icon name="inbox" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-4 text-sm font-semibold text-slate-900">{{ $hasFilters ? __('No matching requests') : __('No incoming requests yet') }}</h3>
                        <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">
                            {{ $hasFilters ? __('Try a different search or filter.') : __('When someone requests one of your physical resources, it will appear here for review.') }}
                        </p>

                        @if ($hasFilters)
                            <a href="{{ route('public.owner-requests.index') }}" class="mt-5 inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                {{ __('Clear filters') }}
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
                                $canManage = ! $item->resource_deleted_at;
                            @endphp

                            <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                <div class="p-5 sm:p-6">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
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

                                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500">
                                                <span class="inline-flex items-center gap-2">
                                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-[10px] font-semibold text-slate-600">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($item->requester_name, 0, 1)) }}</span>
                                                    {{ __('Requested by :name', ['name' => $item->requester_name]) }}
                                                </span>
                                            </div>

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
                                        </div>

                                        <div class="shrink-0">
                                            @if ($item->status === 'pending' && $canManage)
                                                <div class="w-full space-y-2 sm:w-64">
                                                    <form method="POST" action="{{ route('public.owner-requests.update', $item->id) }}" data-confirm="Approving marks the resource as borrowed and blocks new requests until it is returned." data-confirm-title="Approve request?" data-confirm-label="Approve request">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-brand-700">
                                                            <x-icon name="check" class="h-3.5 w-3.5" />
                                                            {{ __('Approve request') }}
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="{{ route('public.owner-requests.update', $item->id) }}" class="space-y-2 rounded-xl border border-rose-200 bg-rose-50/50 p-3" data-confirm="The requester will be notified and the resource will remain available." data-confirm-title="Reject request?" data-confirm-label="Reject request">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="action" value="reject">

                                                        <div>
                                                            <label for="remarks-{{ $item->id }}" class="text-xs font-semibold text-rose-700">{{ __('Remarks') }}</label>
                                                            <textarea id="remarks-{{ $item->id }}" name="remarks" rows="2" maxlength="1000" class="crs-textarea mt-1" placeholder="{{ __('Optional reason (visible to the requester)') }}"></textarea>
                                                        </div>

                                                        <button type="submit" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-rose-200 bg-white px-3.5 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">
                                                            <x-icon name="x-mark" class="h-3.5 w-3.5" />
                                                            {{ __('Reject request') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif ($item->status === 'approved' && $canManage)
                                                <form method="POST" action="{{ route('public.owner-requests.update', $item->id) }}" data-confirm="Confirm that the requester has collected this resource." data-confirm-title="Mark as taken?" data-confirm-label="Mark as taken">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="action" value="picked_up">
                                                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-sky-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-sky-700">
                                                        <x-icon name="check-circle" class="h-3.5 w-3.5" />
                                                        {{ __('Mark as taken') }}
                                                    </button>
                                                </form>
                                            @elseif ($item->status === 'picked_up' && $canManage)
                                                <form method="POST" action="{{ route('public.owner-requests.update', $item->id) }}" data-confirm="Confirm that this resource has been returned. It will become available for new requests." data-confirm-title="Mark as returned?" data-confirm-label="Mark as returned">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="action" value="returned">
                                                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                                        <x-icon name="arrow-path" class="h-3.5 w-3.5" />
                                                        {{ __('Mark as returned') }}
                                                    </button>
                                                </form>
                                            @elseif (! $canManage)
                                                <span class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-2 text-xs font-medium text-slate-500">{{ __('Resource unavailable') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($item->request_message)
                                        <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3">
                                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Message from :name', ['name' => $item->requester_name]) }}</p>
                                            <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $item->request_message }}</p>
                                        </div>
                                    @endif

                                    @if ($item->status === 'rejected' && $item->rejection_reason)
                                        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
                                            <p class="text-xs font-semibold uppercase tracking-wider text-rose-700">{{ __('Your rejection remarks') }}</p>
                                            <p class="mt-1 whitespace-pre-line text-sm leading-6 text-rose-700">{{ $item->rejection_reason }}</p>
                                        </div>
                                    @endif
                                </div>

                                @if ($isOverdue)
                                    <div class="flex items-center gap-2 border-t border-rose-100 bg-rose-50 px-5 py-3 text-xs text-rose-700 sm:px-6">
                                        <x-icon name="exclamation-triangle" class="h-4 w-4 shrink-0" />
                                        {{ __('This loan is past its expected return date. Follow up with the requester.') }}
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


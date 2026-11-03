@php
    $hasFilters = request()->filled('q') || request()->filled('status');
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Borrowing records')"
            :description="__('Review requests and track approvals, pickups, and returns.')"
        />
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-[1440px] space-y-4 px-4 sm:px-6 lg:px-8 2xl:max-w-screen-2xl">
            @include('partials.flash')

            <form method="GET" action="{{ route('borrowing-records.index') }}" class="crs-card p-4 sm:p-5" role="search">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-12">
                    <div class="lg:col-span-7">
                        <label for="borrowing-search" class="crs-label">{{ __('Search') }}</label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input
                                id="borrowing-search"
                                name="q"
                                type="search"
                                value="{{ request('q') }}"
                                placeholder="{{ __('Resource, requester, or owner') }}"
                                class="crs-input ps-9"
                            >
                        </div>
                    </div>

                    <div class="lg:col-span-3">
                        <label for="borrowing-status" class="crs-label">{{ __('Status') }}</label>
                        <select id="borrowing-status" name="status" class="crs-select mt-1.5">
                            <option value="">{{ __('Any status') }}</option>
                            @foreach (['pending', 'approved', 'picked_up', 'returned', 'rejected', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2 lg:col-span-2">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 active:bg-brand-800">
                            <x-icon name="search" class="h-4 w-4" />
                            {{ __('Filter') }}
                        </button>

                        @if ($hasFilters)
                            <x-button-link :href="route('borrowing-records.index')" variant="secondary">
                                {{ __('Reset') }}
                            </x-button-link>
                        @endif
                    </div>
                </div>
            </form>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">
                    {{ trans_choice(':count record|:count records', $borrowingRecords->total(), ['count' => $borrowingRecords->total()]) }}
                </p>
            </div>

            @if ($borrowingRecords->isEmpty())
                <div class="crs-card">
                    <x-empty-state
                        icon="borrowing"
                        :title="$hasFilters ? __('No matching borrowing records') : __('No borrowing records yet')"
                        :description="$hasFilters ? __('Try adjusting your search or filters.') : __('Borrowing requests will appear here once they are created.')"
                    >
                        @if ($hasFilters)
                            <x-slot name="action">
                                <x-button-link :href="route('borrowing-records.index')" variant="secondary">
                                    {{ __('Clear filters') }}
                                </x-button-link>
                            </x-slot>
                        @endif
                    </x-empty-state>
                </div>
            @else
                <div class="crs-table-wrap">
                    <table class="crs-table">
                        <thead class="crs-thead">
                            <tr>
                                <th scope="col" class="crs-th">{{ __('Resource') }}</th>
                                <th scope="col" class="crs-th">{{ __('Requester') }}</th>
                                <th scope="col" class="crs-th hidden lg:table-cell">{{ __('Owner') }}</th>
                                <th scope="col" class="crs-th">{{ __('Status') }}</th>
                                <th scope="col" class="crs-th hidden xl:table-cell">{{ __('Dates') }}</th>
                                <th scope="col" class="crs-th text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($borrowingRecords as $borrowingRecord)
                                @php
                                    $isOverdue = in_array($borrowingRecord->status, ['approved', 'picked_up'], true)
                                        && $borrowingRecord->return_date
                                        && $borrowingRecord->return_date->lt(today());
                                @endphp

                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="crs-td">
                                        <div class="flex items-start gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                                <x-icon name="borrowing" class="h-4 w-4" />
                                            </span>

                                            <div class="min-w-0">
                                                @if ($borrowingRecord->resource)
                                                    <a href="{{ route('resources.show', $borrowingRecord->resource) }}" class="font-semibold text-slate-900 transition hover:text-brand-600">
                                                        {{ $borrowingRecord->resource->title }}
                                                    </a>
                                                @else
                                                    <span class="font-semibold text-slate-500">{{ __('Resource unavailable') }}</span>
                                                @endif

                                                <p class="mt-0.5 text-xs text-slate-500 xl:hidden">
                                                    {{ $borrowingRecord->pickup_date?->format('d M Y') ?: '—' }}
                                                    <span class="mx-1 text-slate-300">&rarr;</span>
                                                    {{ $borrowingRecord->return_date?->format('d M Y') ?: '—' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="crs-td">{{ $borrowingRecord->requester?->name ?: '—' }}</td>
                                    <td class="crs-td hidden lg:table-cell">{{ $borrowingRecord->owner?->name ?: '—' }}</td>

                                    <td class="crs-td">
                                        @if ($isOverdue)
                                            <x-status-badge status="overdue" />
                                        @else
                                            <x-status-badge :status="$borrowingRecord->status" />
                                        @endif
                                    </td>

                                    <td class="crs-td hidden whitespace-nowrap xl:table-cell">
                                        <span class="text-slate-900">{{ $borrowingRecord->pickup_date?->format('d M Y') ?: '—' }}</span>
                                        <span class="mx-1 text-slate-300">&rarr;</span>
                                        <span class="{{ $isOverdue ? 'font-medium text-rose-600' : 'text-slate-900' }}">{{ $borrowingRecord->return_date?->format('d M Y') ?: '—' }}</span>
                                    </td>

                                    <td class="crs-td whitespace-nowrap text-right">
                                        <a href="{{ route('borrowing-records.show', $borrowingRecord) }}" class="crs-link">
                                            {{ $borrowingRecord->owner_id === auth()->id() && $borrowingRecord->status === 'pending' ? __('Review') : __('View') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{ $borrowingRecords->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

@php
    $hasFilters = request()->filled('q');
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Program Setup')"
            :description="__('Manage the programs available to students during registration.')"
        >
            <x-slot name="actions">
                <x-button-link :href="route('programs.create')">
                    <x-icon name="plus" class="h-4 w-4" />
                    {{ __('New program') }}
                </x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-[1440px] space-y-4 px-4 sm:px-6 lg:px-8 2xl:max-w-screen-2xl">
            @include('partials.flash')

            <form method="GET" action="{{ route('programs.index') }}" class="crs-card p-4 sm:p-5" role="search">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="min-w-0 flex-1">
                        <label for="program-search" class="crs-label">{{ __('Search programs') }}</label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input
                                id="program-search"
                                name="q"
                                type="search"
                                value="{{ request('q') }}"
                                placeholder="{{ __('Search by code, name, or description') }}"
                                class="crs-input ps-9"
                            >
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 active:bg-brand-800">
                            <x-icon name="search" class="h-4 w-4" />
                            {{ __('Search') }}
                        </button>

                        @if ($hasFilters)
                            <x-button-link :href="route('programs.index')" variant="secondary">
                                {{ __('Reset') }}
                            </x-button-link>
                        @endif
                    </div>
                </div>
            </form>

            <p class="text-sm text-slate-500">
                {{ trans_choice(':count program|:count programs', $programs->total(), ['count' => $programs->total()]) }}
            </p>

            @if ($programs->isEmpty())
                <div class="crs-card">
                    <x-empty-state
                        icon="academic-cap"
                        :title="$hasFilters ? __('No matching programs') : __('No programs yet')"
                        :description="$hasFilters ? __('Try a different search term or clear your filters.') : __('Create a program so students can select it during registration.')"
                    >
                        <x-slot name="action">
                            @if ($hasFilters)
                                <x-button-link :href="route('programs.index')" variant="secondary">
                                    {{ __('Clear search') }}
                                </x-button-link>
                            @else
                                <x-button-link :href="route('programs.create')">
                                    <x-icon name="plus" class="h-4 w-4" />
                                    {{ __('Create program') }}
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
                                <th scope="col" class="crs-th">{{ __('Code') }}</th>
                                <th scope="col" class="crs-th">{{ __('Program name') }}</th>
                                <th scope="col" class="crs-th hidden md:table-cell">{{ __('Description') }}</th>
                                <th scope="col" class="crs-th">{{ __('Students') }}</th>
                                <th scope="col" class="crs-th">{{ __('Status') }}</th>
                                <th scope="col" class="crs-th text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($programs as $program)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="crs-td">
                                        <span class="inline-flex items-center rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700">
                                            {{ $program->code }}
                                        </span>
                                    </td>

                                    <td class="crs-td">
                                        <p class="font-semibold text-slate-900">{{ $program->name }}</p>
                                        <p class="mt-0.5 max-w-xs truncate text-xs text-slate-500 md:hidden">
                                            {{ \Illuminate\Support\Str::limit($program->description ?: __('No description'), 60) }}
                                        </p>
                                    </td>

                                    <td class="crs-td hidden max-w-md md:table-cell">
                                        <span class="line-clamp-2">{{ $program->description ?: '—' }}</span>
                                    </td>

                                    <td class="crs-td">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                            <x-icon name="users" class="h-3.5 w-3.5" />
                                            {{ number_format($program->users_count) }}
                                        </span>
                                    </td>

                                    <td class="crs-td">
                                        @if ($program->is_active)
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">{{ __('Active') }}</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>

                                    <td class="crs-td whitespace-nowrap text-right">
                                        <a href="{{ route('programs.edit', $program) }}" class="crs-link">{{ __('Edit') }}</a>

                                        <form method="POST" action="{{ route('programs.destroy', $program) }}" class="ms-3 inline" data-confirm="This program will be permanently deleted. Programs assigned to students cannot be removed." data-confirm-title="Delete program?" data-confirm-label="Delete program">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="crs-danger-link">{{ __('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{ $programs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

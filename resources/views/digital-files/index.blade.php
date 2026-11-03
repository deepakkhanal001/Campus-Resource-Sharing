@php
    $isAdmin = auth()->user()->hasRole('admin');
    $hasFilters = request()->filled('q');
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Digital files')"
            :description="__('Manage files attached to digital resources.')"
        >
            <x-slot name="actions">
                <x-button-link :href="route('digital-files.create')">
                    <x-icon name="upload" class="h-4 w-4" />
                    {{ __('Upload file') }}
                </x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-[1440px] space-y-4 px-4 sm:px-6 lg:px-8 2xl:max-w-screen-2xl">
            @include('partials.flash')

            <form method="GET" action="{{ route('digital-files.index') }}" class="crs-card p-4 sm:p-5" role="search">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="min-w-0 flex-1">
                        <label for="file-search" class="crs-label">{{ __('Search files') }}</label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input
                                id="file-search"
                                name="q"
                                type="search"
                                value="{{ request('q') }}"
                                placeholder="{{ __('File or resource name') }}"
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
                            <x-button-link :href="route('digital-files.index')" variant="secondary">
                                {{ __('Reset') }}
                            </x-button-link>
                        @endif
                    </div>
                </div>
            </form>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">
                    {{ trans_choice(':count file|:count files', $digitalFiles->total(), ['count' => $digitalFiles->total()]) }}
                </p>
            </div>

            @if ($digitalFiles->isEmpty())
                <div class="crs-card">
                    <x-empty-state
                        icon="document"
                        :title="$hasFilters ? __('No matching files') : __('No digital files yet')"
                        :description="$hasFilters ? __('Try a different search term or clear your filters.') : __('Upload a file and attach it to a digital resource.')"
                    >
                        <x-slot name="action">
                            @if ($hasFilters)
                                <x-button-link :href="route('digital-files.index')" variant="secondary">
                                    {{ __('Clear search') }}
                                </x-button-link>
                            @else
                                <x-button-link :href="route('digital-files.create')">
                                    <x-icon name="upload" class="h-4 w-4" />
                                    {{ __('Upload file') }}
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
                                <th scope="col" class="crs-th">{{ __('File') }}</th>
                                <th scope="col" class="crs-th">{{ __('Resource') }}</th>
                                <th scope="col" class="crs-th hidden md:table-cell">{{ __('Type') }}</th>
                                <th scope="col" class="crs-th hidden sm:table-cell">{{ __('Size') }}</th>
                                <th scope="col" class="crs-th hidden lg:table-cell">{{ __('Downloads') }}</th>
                                <th scope="col" class="crs-th text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($digitalFiles as $digitalFile)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="crs-td">
                                        <div class="flex items-start gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                                <x-icon name="document" class="h-4 w-4" />
                                            </span>
                                            <div class="min-w-0">
                                                <a href="{{ route('digital-files.show', $digitalFile) }}" class="break-words font-semibold text-slate-900 transition hover:text-brand-600">
                                                    {{ $digitalFile->file_name }}
                                                </a>
                                                <p class="mt-0.5 text-xs text-slate-500 md:hidden">{{ $digitalFile->file_type }} · <x-file-size :bytes="$digitalFile->size" /></p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="crs-td">
                                        @if ($digitalFile->resource)
                                            <a href="{{ route('resources.show', $digitalFile->resource) }}" class="crs-link">
                                                {{ $digitalFile->resource->title }}
                                            </a>
                                        @else
                                            <span class="text-slate-400">{{ __('Resource unavailable') }}</span>
                                        @endif
                                    </td>

                                    <td class="crs-td hidden md:table-cell">{{ $digitalFile->file_type }}</td>
                                    <td class="crs-td hidden whitespace-nowrap sm:table-cell"><x-file-size :bytes="$digitalFile->size" /></td>
                                    <td class="crs-td hidden whitespace-nowrap lg:table-cell">{{ number_format((int) $digitalFile->download_count) }}</td>

                                    <td class="crs-td whitespace-nowrap text-right">
                                        <a href="{{ route('digital-files.show', $digitalFile) }}" class="crs-link">{{ __('View') }}</a>
                                        <a href="{{ route('digital-files.download', $digitalFile) }}" class="crs-link ms-3">{{ __('Download') }}</a>

                                        @if ($isAdmin || $digitalFile->resource?->owner_id === auth()->id())
                                            <a href="{{ route('digital-files.edit', $digitalFile) }}" class="crs-link ms-3">{{ __('Edit') }}</a>

                                            <form method="POST" action="{{ route('digital-files.destroy', $digitalFile) }}" class="ms-3 inline" data-confirm="This file will be permanently deleted from the resource." data-confirm-title="Delete file?" data-confirm-label="Delete file">
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

                    {{ $digitalFiles->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

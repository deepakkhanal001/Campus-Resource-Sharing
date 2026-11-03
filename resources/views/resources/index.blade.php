@php
    $isAdmin = auth()->user()->hasRole('admin');
    $hasFilters = request()->filled('q')
        || request()->filled('type')
        || request()->filled('availability_status')
        || request()->filled('category_id');
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="$isAdmin ? __('Resources') : __('My resources')"
            :description="$isAdmin
                ? __('Browse, filter, and manage every resource in the shared library.')
                : __('Manage the resources you have shared with the campus community.')"
        >
            <x-slot name="actions">
                <x-button-link :href="route('public.resources.index')" variant="secondary">
                    <x-icon name="collection" class="h-4 w-4" />
                    {{ __('Browse library') }}
                </x-button-link>

                <x-button-link :href="route('resources.create')">
                    <x-icon name="plus" class="h-4 w-4" />
                    {{ __('New resource') }}
                </x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-[1440px] space-y-4 px-4 sm:px-6 lg:px-8 2xl:max-w-screen-2xl">
            @include('partials.flash')

            <form method="GET" action="{{ route('resources.index') }}" class="crs-card p-4 sm:p-5" role="search">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-12">
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label for="resource-search" class="crs-label">{{ __('Search') }}</label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input
                                id="resource-search"
                                name="q"
                                type="search"
                                value="{{ request('q') }}"
                                placeholder="{{ __('Title or description') }}"
                                class="crs-input ps-9"
                            >
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="resource-type" class="crs-label">{{ __('Type') }}</label>
                        <select id="resource-type" name="type" class="crs-select mt-1.5">
                            <option value="">{{ __('All types') }}</option>
                            <option value="physical" @selected(request('type') === 'physical')>{{ __('Physical') }}</option>
                            <option value="digital" @selected(request('type') === 'digital')>{{ __('Digital') }}</option>
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="resource-availability" class="crs-label">{{ __('Availability') }}</label>
                        <select id="resource-availability" name="availability_status" class="crs-select mt-1.5">
                            <option value="">{{ __('Any status') }}</option>
                            <option value="available" @selected(request('availability_status') === 'available')>{{ __('Available') }}</option>
                            <option value="borrowed" @selected(request('availability_status') === 'borrowed')>{{ __('Borrowed') }}</option>
                            <option value="unavailable" @selected(request('availability_status') === 'unavailable')>{{ __('Unavailable') }}</option>
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="resource-category" class="crs-label">{{ __('Category') }}</label>
                        <select id="resource-category" name="category_id" class="crs-select mt-1.5">
                            <option value="">{{ __('All categories') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 active:bg-brand-800">
                            <x-icon name="search" class="h-4 w-4" />
                            {{ __('Filter') }}
                        </button>

                        @if ($hasFilters)
                            <x-button-link :href="route('resources.index')" variant="secondary" class="px-3">
                                {{ __('Reset') }}
                            </x-button-link>
                        @endif
                    </div>
                </div>
            </form>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">
                    {{ trans_choice(':count resource|:count resources', $resources->total(), ['count' => $resources->total()]) }}
                </p>
            </div>

            @if ($resources->isEmpty())
                <div class="crs-card">
                    <x-empty-state
                        icon="collection"
                        :title="$hasFilters ? __('No matching resources') : __('No resources yet')"
                        :description="$hasFilters ? __('Try adjusting your search or filters to find what you need.') : __('Share the first resource to get the library started.')"
                    >
                        <x-slot name="action">
                            @if ($hasFilters)
                                <x-button-link :href="route('resources.index')" variant="secondary">
                                    {{ __('Clear filters') }}
                                </x-button-link>
                            @else
                                <x-button-link :href="route('resources.create')">
                                    <x-icon name="plus" class="h-4 w-4" />
                                    {{ __('Add resource') }}
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
                                <th scope="col" class="crs-th">{{ __('Resource') }}</th>
                                <th scope="col" class="crs-th hidden md:table-cell">{{ __('Type') }}</th>
                                <th scope="col" class="crs-th hidden lg:table-cell">{{ __('Category') }}</th>
                                <th scope="col" class="crs-th hidden xl:table-cell">{{ __('Owner') }}</th>
                                <th scope="col" class="crs-th">{{ __('Availability') }}</th>
                                <th scope="col" class="crs-th text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($resources as $resource)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="crs-td">
                                        <div class="flex items-start gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                                <x-icon :name="$resource->type === 'digital' ? 'document' : 'collection'" class="h-4 w-4" />
                                            </span>

                                            <div class="min-w-0">
                                                <a href="{{ route('resources.show', $resource->id) }}" class="font-semibold text-slate-900 transition hover:text-brand-600">
                                                    {{ $resource->title }}
                                                </a>
                                                <p class="mt-0.5 text-xs text-slate-500 md:hidden">
                                                    {{ ucfirst($resource->type) }}
                                                    <span class="mx-1 text-slate-300">·</span>
                                                    {{ $resource->category_name ?: __('Uncategorised') }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="crs-td hidden md:table-cell">{{ ucfirst($resource->type) }}</td>
                                    <td class="crs-td hidden lg:table-cell">{{ $resource->category_name ?: '—' }}</td>
                                    <td class="crs-td hidden xl:table-cell">{{ $resource->owner_name ?: '—' }}</td>
                                    <td class="crs-td"><x-status-badge :status="$resource->availability_status" /></td>

                                    <td class="crs-td whitespace-nowrap text-right">
                                        <a href="{{ route('resources.show', $resource->id) }}" class="crs-link">{{ __('View') }}</a>

                                        @if ($isAdmin || $resource->owner_id == auth()->id())
                                            <a href="{{ route('resources.edit', $resource->id) }}" class="crs-link ms-3">{{ __('Edit') }}</a>

                                            <form method="POST" action="{{ route('resources.destroy', $resource->id) }}" class="ms-3 inline" data-confirm="This resource and its related records will be removed from the library." data-confirm-title="Delete resource?" data-confirm-label="Delete resource">
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

                    {{ $resources->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

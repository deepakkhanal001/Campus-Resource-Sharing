@php
    $isAdmin = auth()->user()->hasRole('admin');
    $hasFilters = request()->filled('q');
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Categories')"
            :description="__('Organize resources into clear, easy-to-browse groups.')"
        >
            @if ($isAdmin)
                <x-slot name="actions">
                    <x-button-link :href="route('categories.create')">
                        <x-icon name="plus" class="h-4 w-4" />
                        {{ __('New category') }}
                    </x-button-link>
                </x-slot>
            @endif
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-[1440px] space-y-4 px-4 sm:px-6 lg:px-8 2xl:max-w-screen-2xl">
            @include('partials.flash')

            <form method="GET" action="{{ route('categories.index') }}" class="crs-card p-4 sm:p-5" role="search">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="min-w-0 flex-1">
                        <label for="category-search" class="crs-label">{{ __('Search categories') }}</label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input
                                id="category-search"
                                name="q"
                                type="search"
                                value="{{ request('q') }}"
                                placeholder="{{ __('Search by name or description') }}"
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
                            <x-button-link :href="route('categories.index')" variant="secondary">
                                {{ __('Reset') }}
                            </x-button-link>
                        @endif
                    </div>
                </div>
            </form>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">
                    {{ trans_choice(':count category|:count categories', $categories->total(), ['count' => $categories->total()]) }}
                </p>
            </div>

            @if ($categories->isEmpty())
                <div class="crs-card">
                    <x-empty-state
                        icon="folder"
                        :title="$hasFilters ? __('No matching categories') : __('No categories yet')"
                        :description="$hasFilters ? __('Try a different search term or clear your filters.') : __('Create a category to start organizing your resource library.')"
                    >
                        <x-slot name="action">
                            @if ($hasFilters)
                                <x-button-link :href="route('categories.index')" variant="secondary">
                                    {{ __('Clear search') }}
                                </x-button-link>
                            @elseif ($isAdmin)
                                <x-button-link :href="route('categories.create')">
                                    <x-icon name="plus" class="h-4 w-4" />
                                    {{ __('Create category') }}
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
                                <th scope="col" class="crs-th">{{ __('Name') }}</th>
                                <th scope="col" class="crs-th hidden md:table-cell">{{ __('Description') }}</th>
                                <th scope="col" class="crs-th">{{ __('Resources') }}</th>
                                <th scope="col" class="crs-th text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($categories as $category)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="crs-td">
                                        <a href="{{ route('categories.show', $category) }}" class="font-semibold text-slate-900 transition hover:text-brand-600">
                                            {{ $category->name }}
                                        </a>
                                        <p class="mt-0.5 max-w-xs truncate text-xs text-slate-500 md:hidden">
                                            {{ \Illuminate\Support\Str::limit($category->description ?: __('No description'), 60) }}
                                        </p>
                                    </td>

                                    <td class="crs-td hidden max-w-md md:table-cell">
                                        <span class="line-clamp-2">{{ $category->description ?: '—' }}</span>
                                    </td>

                                    <td class="crs-td">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                            <x-icon name="collection" class="h-3.5 w-3.5" />
                                            {{ number_format($category->resources_count) }}
                                        </span>
                                    </td>

                                    <td class="crs-td whitespace-nowrap text-right">
                                        <a href="{{ route('categories.show', $category) }}" class="crs-link">View</a>

                                        @if ($isAdmin)
                                            <a href="{{ route('categories.edit', $category) }}" class="crs-link ms-3">Edit</a>

                                            <form method="POST" action="{{ route('categories.destroy', $category) }}" class="ms-3 inline" data-confirm="This category will be permanently deleted. Categories that still contain resources cannot be removed." data-confirm-title="Delete category?" data-confirm-label="Delete category">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="crs-danger-link">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

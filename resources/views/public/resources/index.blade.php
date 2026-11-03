<x-public-layout :title="__('Browse resources')">
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-[1440px] px-4 py-7 sm:px-6 lg:px-8 lg:py-9">
            <nav class="flex items-center gap-1.5 text-xs text-slate-500" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('public.home') }}" class="transition hover:text-slate-900">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <span class="font-medium text-slate-700">{{ __('Browse') }}</span>
            </nav>

            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="font-display text-[30px] font-bold tracking-tight text-slate-900 sm:text-[36px]">{{ __('Browse resources') }}</h1>
                    <p class="mt-2 max-w-2xl text-[13px] leading-5 text-slate-500">{{ __('Search the shared library, filter by what you need, and open any listing to see availability and details.') }}</p>
                </div>

                <p class="shrink-0 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-600">
                    {{ trans_choice(':count resource|:count resources', $resources->total(), ['count' => $resources->total()]) }}
                </p>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-[1440px] px-4 py-7 sm:px-6 lg:px-8 lg:py-8">
        <div class="grid min-w-0 gap-5 lg:grid-cols-[16rem_minmax(0,1fr)] lg:items-start lg:gap-6">
            <aside class="min-w-0 lg:sticky lg:top-[88px]">
                <form method="GET" action="{{ route('public.resources.index') }}" class="crs-card overflow-hidden" role="search">
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3.5">
                        <h2 class="text-sm font-semibold text-slate-900">{{ __('Filters') }}</h2>
                        @if ($hasFilters)
                            <a href="{{ route('public.resources.index') }}" class="text-xs font-semibold text-brand-600 transition hover:text-brand-800">{{ __('Reset all') }}</a>
                        @endif
                    </div>

                    <div class="space-y-4 p-4">
                        <div>
                            <label for="resource-search" class="crs-label">{{ __('Search') }}</label>
                            <div class="relative mt-1.5">
                                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                                    <x-icon name="search" class="h-4 w-4" />
                                </span>
                                <input
                                    id="resource-search"
                                    name="q"
                                    type="search"
                                    value="{{ $filters['q'] }}"
                                    placeholder="{{ __('Title or description') }}"
                                    class="crs-input ps-9"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="resource-category" class="crs-label">{{ __('Category') }}</label>
                            <select id="resource-category" name="category_id" class="crs-select mt-1.5">
                                <option value="">{{ __('All categories') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) $filters['category_id'] === (int) $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="resource-type" class="crs-label">{{ __('Resource type') }}</label>
                            <select id="resource-type" name="type" class="crs-select mt-1.5">
                                <option value="">{{ __('Any type') }}</option>
                                <option value="physical" @selected($filters['type'] === 'physical')>{{ __('Physical') }}</option>
                                <option value="digital" @selected($filters['type'] === 'digital')>{{ __('Digital') }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="resource-availability" class="crs-label">{{ __('Availability') }}</label>
                            <select id="resource-availability" name="availability" class="crs-select mt-1.5">
                                <option value="">{{ __('Any availability') }}</option>
                                <option value="available" @selected($filters['availability'] === 'available')>{{ __('Available') }}</option>
                                <option value="borrowed" @selected($filters['availability'] === 'borrowed')>{{ __('Borrowed') }}</option>
                                <option value="unavailable" @selected($filters['availability'] === 'unavailable')>{{ __('Unavailable') }}</option>
                            </select>
                        </div>

                        <div>
                            <label for="resource-sort" class="crs-label">{{ __('Sort by') }}</label>
                            <select id="resource-sort" name="sort" class="crs-select mt-1.5">
                                <option value="newest" @selected($filters['sort'] === 'newest')>{{ __('Newest first') }}</option>
                                <option value="oldest" @selected($filters['sort'] === 'oldest')>{{ __('Oldest first') }}</option>
                                <option value="title" @selected($filters['sort'] === 'title')>{{ __('Title A-Z') }}</option>
                            </select>
                        </div>

                        <button type="submit" class="crs-btn crs-btn-primary w-full">
                            <x-icon name="search" class="h-4 w-4" />
                            {{ __('Apply filters') }}
                        </button>
                    </div>
                </form>
            </aside>

            <section class="min-w-0" aria-labelledby="resource-results-title">
                <h2 id="resource-results-title" class="sr-only">{{ __('Resource results') }}</h2>

                @if ($resources->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                            <x-icon name="search" class="h-6 w-6" />
                        </span>
                        <h3 class="mt-4 text-sm font-semibold text-slate-900">{{ $hasFilters ? __('No resources match your filters') : __('No resources are available yet') }}</h3>
                        <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">
                            {{ $hasFilters ? __('Try a broader search or reset the filters to see the full library.') : __('New resources will appear here as soon as they are shared by the campus community.') }}
                        </p>

                        @if ($hasFilters)
                            <a href="{{ route('public.resources.index') }}" class="mt-5 inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                {{ __('Clear filters') }}
                            </a>
                        @endif
                    </div>
                @else
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($resources as $resource)
                            <x-public-resource-card :resource="$resource" />
                        @endforeach
                    </div>

                    <div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-white">
                        {{ $resources->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-public-layout>

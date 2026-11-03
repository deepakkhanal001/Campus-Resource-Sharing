<x-public-layout :title="__('Find and share campus resources')">
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto grid max-w-[1440px] items-center gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[1.12fr_0.88fr] lg:gap-14 lg:px-8 lg:py-14">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full border border-brand-100 bg-brand-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-brand-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-600" aria-hidden="true"></span>
                    {{ __('Gomendra Multiple College') }}
                </p>

                <h1 class="mt-5 max-w-2xl font-display text-[34px] font-bold leading-[1.08] tracking-tight text-slate-900 sm:text-[44px]">
                    {{ __('Borrow what you need. Share what you have.') }}
                </h1>

                <p class="mt-4 max-w-xl text-[15px] leading-7 text-slate-600">
                    {{ __('Discover physical resources and digital materials shared by the campus community. Request a borrow, download files, and keep every handoff organized in one place.') }}
                </p>

                <form method="GET" action="{{ route('public.resources.index') }}" class="mt-7 max-w-xl" role="search">
                    <label for="home-search" class="sr-only">{{ __('Search resources') }}</label>
                    <div class="flex flex-col gap-2 rounded-xl border border-slate-200 bg-white p-1.5 shadow-card focus-within:border-brand-300 focus-within:ring-4 focus-within:ring-brand-500/10 sm:flex-row">
                        <div class="relative min-w-0 flex-1">
                            <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                                <x-icon name="search" class="h-5 w-5" />
                            </span>
                            <input
                                id="home-search"
                                name="q"
                                type="search"
                                placeholder="{{ __('Search books, equipment, notes...') }}"
                                class="h-11 w-full rounded-lg border-0 bg-transparent pe-4 ps-10 text-sm text-slate-900 placeholder:text-slate-400 focus:ring-0"
                            >
                        </div>

                        <button type="submit" class="crs-btn crs-btn-primary h-11 px-5">
                            {{ __('Search') }}
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </button>
                    </div>
                </form>

                <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-[13px] text-slate-500">
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="check-circle" class="h-4 w-4 text-emerald-600" />
                        {{ __('Free to browse') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="shield-check" class="h-4 w-4 text-emerald-600" />
                        {{ __('Owner-approved requests') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="download" class="h-4 w-4 text-emerald-600" />
                        {{ __('Secure downloads') }}
                    </span>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-[#0b1424] p-6 text-white shadow-xl sm:p-7">
                <div
                    class="pointer-events-none absolute inset-0 opacity-[0.055]"
                    style="background-image: linear-gradient(to right, #fff 1px, transparent 1px), linear-gradient(to bottom, #fff 1px, transparent 1px); background-size: 32px 32px;"
                    aria-hidden="true"
                ></div>
                <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-brand-500/20 blur-2xl" aria-hidden="true"></div>

                <div class="relative flex items-center justify-between gap-4 border-b border-white/10 pb-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-brand-300">{{ __('Campus library') }}</p>
                        <h2 class="mt-1 font-display text-lg font-bold text-white">{{ __('Library at a glance') }}</h2>
                    </div>

                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/[0.06] text-brand-300 ring-1 ring-inset ring-white/10">
                        <x-icon name="collection" class="h-5 w-5" />
                    </span>
                </div>

                <dl class="relative mt-5 grid grid-cols-3 gap-3">
                    <div class="rounded-lg bg-white/[0.05] p-3 ring-1 ring-inset ring-white/[0.06]">
                        <dt class="text-[10px] font-medium uppercase tracking-wide text-slate-400">{{ __('Available') }}</dt>
                        <dd class="mt-1.5 font-display text-2xl font-bold text-white">{{ number_format($stats['available']) }}</dd>
                    </div>
                    <div class="rounded-lg bg-white/[0.05] p-3 ring-1 ring-inset ring-white/[0.06]">
                        <dt class="text-[10px] font-medium uppercase tracking-wide text-slate-400">{{ __('Physical') }}</dt>
                        <dd class="mt-1.5 font-display text-2xl font-bold text-white">{{ number_format($stats['physical']) }}</dd>
                    </div>
                    <div class="rounded-lg bg-white/[0.05] p-3 ring-1 ring-inset ring-white/[0.06]">
                        <dt class="text-[10px] font-medium uppercase tracking-wide text-slate-400">{{ __('Digital') }}</dt>
                        <dd class="mt-1.5 font-display text-2xl font-bold text-white">{{ number_format($stats['digital']) }}</dd>
                    </div>
                </dl>

                <div class="relative mt-5 flex items-center justify-between gap-4">
                    <p class="max-w-[15rem] text-xs leading-5 text-slate-400">{{ __('Open to students and staff across the college.') }}</p>
                    <a href="{{ route('public.resources.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-2 text-xs font-semibold text-white ring-1 ring-inset ring-white/10 transition hover:bg-white/15">
                        {{ __('Browse') }}
                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white" aria-labelledby="home-categories-title">
        <div class="mx-auto max-w-[1440px] px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="crs-eyebrow">{{ __('Browse by category') }}</p>
                    <h2 id="home-categories-title" class="mt-1.5 font-display text-2xl font-bold tracking-tight text-slate-900">{{ __('Find the right shelf') }}</h2>
                </div>

                <a href="{{ route('public.categories.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 transition hover:text-brand-900">
                    {{ __('All categories') }}
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            @if (count($categories) > 0)
                <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach (array_slice($categories, 0, 6) as $category)
                        <a href="{{ route('public.resources.index', ['category_id' => $category->id]) }}" class="group flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-card transition hover:border-brand-200 hover:shadow-lift">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100 transition group-hover:bg-brand-700 group-hover:text-white">
                                <x-icon name="folder" class="h-5 w-5" />
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-3">
                                    <span class="truncate font-semibold text-slate-900 transition group-hover:text-brand-800">{{ $category->name }}</span>
                                    <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">{{ number_format((int) $category->resources_count) }}</span>
                                </span>
                                <span class="mt-1 line-clamp-2 block text-[13px] leading-5 text-slate-500">{{ $category->description ?: __('Resources grouped under :name.', ['name' => $category->name]) }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
                    <x-icon name="folder" class="mx-auto h-6 w-6 text-slate-400" />
                    <p class="mt-3 text-sm font-semibold text-slate-900">{{ __('No categories yet') }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Categories will appear here as resources are organized.') }}</p>
                </div>
            @endif
        </div>
    </section>

    <section class="border-y border-slate-200 bg-slate-50" aria-labelledby="home-latest-title">
        <div class="mx-auto max-w-[1440px] px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="crs-eyebrow">{{ __('Recently shared') }}</p>
                    <h2 id="home-latest-title" class="mt-1.5 font-display text-2xl font-bold tracking-tight text-slate-900">{{ __('New on the shelves') }}</h2>
                    <p class="mt-1.5 max-w-2xl text-[13px] leading-5 text-slate-500">{{ __('The latest available physical and digital resources from the campus community.') }}</p>
                </div>

                <a href="{{ route('public.resources.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 transition hover:text-brand-900">
                    {{ __('Browse everything') }}
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            @if (count($resources) > 0)
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($resources as $resource)
                        <x-public-resource-card :resource="$resource" />
                    @endforeach
                </div>
            @else
                <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                    <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <x-icon name="collection" class="h-5 w-5" />
                    </span>
                    <h3 class="mt-3 text-sm font-semibold text-slate-900">{{ __('No resources available yet') }}</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">{{ __('When students and staff share resources, they will appear here for everyone to discover.') }}</p>
                </div>
            @endif
        </div>
    </section>

    <section class="bg-white" aria-labelledby="home-how-title">
        <div class="mx-auto max-w-[1440px] px-4 py-12 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="crs-eyebrow">{{ __('How it works') }}</p>
                <h2 id="home-how-title" class="mt-1.5 font-display text-2xl font-bold tracking-tight text-slate-900">{{ __('From discovery to handoff') }}</h2>
                <p class="mt-2 text-[13px] leading-5 text-slate-500">{{ __('A clear, owner-approved borrowing flow keeps every resource accountable without getting in the way.') }}</p>
            </div>

            <div class="mt-7 grid gap-6 md:grid-cols-3 md:gap-8">
                <div class="relative border-t-2 border-slate-200 pt-5">
                    <span class="absolute -top-3 left-0 inline-flex h-6 items-center rounded-full bg-brand-700 px-2 text-[10px] font-bold uppercase tracking-wider text-white">{{ __('Step 1') }}</span>
                    <h3 class="mt-2 flex items-center gap-2 font-display text-base font-bold text-slate-900">
                        <x-icon name="search" class="h-4 w-4 text-brand-700" />
                        {{ __('Find a resource') }}
                    </h3>
                    <p class="mt-2 text-[13px] leading-6 text-slate-500">{{ __('Search by title, filter by category or type, and check availability before you request.') }}</p>
                </div>

                <div class="relative border-t-2 border-slate-200 pt-5">
                    <span class="absolute -top-3 left-0 inline-flex h-6 items-center rounded-full bg-brand-700 px-2 text-[10px] font-bold uppercase tracking-wider text-white">{{ __('Step 2') }}</span>
                    <h3 class="mt-2 flex items-center gap-2 font-display text-base font-bold text-slate-900">
                        <x-icon name="send" class="h-4 w-4 text-brand-700" />
                        {{ __('Send a request') }}
                    </h3>
                    <p class="mt-2 text-[13px] leading-6 text-slate-500">{{ __('Add a short message and preferred dates. The owner reviews the request directly.') }}</p>
                </div>

                <div class="relative border-t-2 border-slate-200 pt-5">
                    <span class="absolute -top-3 left-0 inline-flex h-6 items-center rounded-full bg-brand-700 px-2 text-[10px] font-bold uppercase tracking-wider text-white">{{ __('Step 3') }}</span>
                    <h3 class="mt-2 flex items-center gap-2 font-display text-base font-bold text-slate-900">
                        <x-icon name="check-circle" class="h-4 w-4 text-brand-700" />
                        {{ __('Pick up and return') }}
                    </h3>
                    <p class="mt-2 text-[13px] leading-6 text-slate-500">{{ __('Track approval, pickup, and return status. The resource becomes available again after return.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white pb-12">
        <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-2xl bg-[#0b1424] px-6 py-9 text-white shadow-xl sm:px-10 sm:py-10">
                <div
                    class="pointer-events-none absolute inset-0 opacity-[0.05]"
                    style="background-image: linear-gradient(to right, #fff 1px, transparent 1px), linear-gradient(to bottom, #fff 1px, transparent 1px); background-size: 32px 32px;"
                    aria-hidden="true"
                ></div>

                <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div class="max-w-2xl">
                        <h2 class="font-display text-2xl font-bold tracking-tight text-white">{{ __('Have something gathering dust?') }}</h2>
                        <p class="mt-2 text-[13px] leading-6 text-slate-400">{{ __('Put it back into circulation. Share a resource with the campus community and help someone else get their work done.') }}</p>
                    </div>

                    <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
                        <a href="{{ route('public.resources.index') }}" class="crs-btn bg-white text-slate-900 shadow-sm hover:bg-slate-100 focus-visible:ring-white">
                            {{ __('Browse resources') }}
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>

                        @auth
                            <a href="{{ route('resources.create') }}" class="crs-btn border border-white/20 bg-white/5 text-white hover:bg-white/10 focus-visible:ring-brand-300">
                                <x-icon name="plus" class="h-4 w-4" />
                                {{ __('Share a resource') }}
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="crs-btn border border-white/20 bg-white/5 text-white hover:bg-white/10 focus-visible:ring-brand-300">
                                {{ __('Create an account') }}
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>

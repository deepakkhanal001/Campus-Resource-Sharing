<x-public-layout :title="__('Categories')">
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-[1440px] px-4 py-7 sm:px-6 lg:px-8 lg:py-9">
            <nav class="flex items-center gap-1.5 text-xs text-slate-500" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('public.home') }}" class="transition hover:text-slate-900">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <span class="font-medium text-slate-700">{{ __('Categories') }}</span>
            </nav>

            <div class="mt-4">
                <h1 class="font-display text-[30px] font-bold tracking-tight text-slate-900 sm:text-[36px]">{{ __('Browse categories') }}</h1>
                <p class="mt-2 max-w-2xl text-[13px] leading-5 text-slate-500">{{ __('Explore the shared library by subject and resource type. Counts show resources that are currently available.') }}</p>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-[1440px] px-4 py-7 sm:px-6 lg:px-8 lg:py-8">
        @if (count($categories) > 0)
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <a href="{{ route('public.resources.index', ['category_id' => $category->id]) }}" class="group flex h-full flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-lift">
                        <div class="flex items-start justify-between gap-4">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100 transition group-hover:bg-brand-700 group-hover:text-white">
                                <x-icon name="folder" class="h-5 w-5" />
                            </span>

                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600">
                                {{ trans_choice(':count available|:count available', (int) $category->resources_count, ['count' => (int) $category->resources_count]) }}
                            </span>
                        </div>

                        <h2 class="mt-4 font-display text-base font-bold text-slate-900 transition group-hover:text-brand-800">{{ $category->name }}</h2>
                        <p class="mt-1.5 flex-1 text-[13px] leading-5 text-slate-500">{{ $category->description ?: __('Resources grouped under this category.') }}</p>

                        <span class="mt-4 inline-flex items-center gap-1.5 text-[13px] font-semibold text-brand-700">
                            {{ __('View resources') }}
                            <x-icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-0.5" />
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                    <x-icon name="folder" class="h-5 w-5" />
                </span>
                <h2 class="mt-3 text-sm font-semibold text-slate-900">{{ __('No categories yet') }}</h2>
                <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-500">{{ __('Categories will appear here once resources have been organized by the campus community.') }}</p>
                <a href="{{ route('public.resources.index') }}" class="crs-btn crs-btn-primary mt-5">
                    {{ __('Browse all resources') }}
                </a>
            </div>
        @endif
    </div>
</x-public-layout>

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <p class="text-sm text-slate-500">
            {{ __('Showing') }}
            <span class="font-semibold text-slate-700">{{ $paginator->firstItem() }}</span>
            {{ __('to') }}
            <span class="font-semibold text-slate-700">{{ $paginator->lastItem() }}</span>
            {{ __('of') }}
            <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span>
            {{ __('results') }}
        </p>

        <div class="flex items-center justify-between gap-2 sm:justify-end">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-400" aria-disabled="true">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    <span class="hidden sm:inline">{{ __('Previous') }}</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    <span class="hidden sm:inline">{{ __('Previous') }}</span>
                </a>
            @endif

            <span class="text-sm text-slate-500 sm:hidden">
                {{ __('Page :current of :last', ['current' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}
            </span>

            <div class="hidden items-center gap-1 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex h-9 min-w-9 items-center justify-center px-2 text-sm text-slate-400" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg bg-brand-600 px-2 text-sm font-semibold text-white" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                    <span class="hidden sm:inline">{{ __('Next') }}</span>
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-400" aria-disabled="true">
                    <span class="hidden sm:inline">{{ __('Next') }}</span>
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </span>
            @endif
        </div>
    </nav>
@endif

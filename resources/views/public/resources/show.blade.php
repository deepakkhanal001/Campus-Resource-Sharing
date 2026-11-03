@php
    $isDigital = $resource->type === 'digital';
    $createdAt = $resource->created_at
        ? \Illuminate\Support\Carbon::parse($resource->created_at)->format('d M Y')
        : null;
    $ownerInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($resource->owner_name ?? 'U', 0, 1));
    $availabilityLabel = match ($resource->availability_status) {
        'available' => __('Available'),
        'borrowed' => __('Borrowed'),
        default => __('Unavailable'),
    };
@endphp

<x-public-layout :title="$resource->title">
    <div class="mx-auto max-w-[1440px] px-4 py-7 sm:px-6 lg:px-8 lg:py-8">
        <nav class="flex flex-wrap items-center gap-1.5 text-xs text-slate-500" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('public.home') }}" class="transition hover:text-slate-900">{{ __('Home') }}</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('public.resources.index') }}" class="transition hover:text-slate-900">{{ __('Browse') }}</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('public.resources.index', ['category_id' => $resource->category_id]) }}" class="transition hover:text-slate-900">{{ $resource->category_name }}</a>
            <span aria-hidden="true">/</span>
            <span class="max-w-[16rem] truncate font-medium text-slate-700 sm:max-w-none">{{ $resource->title }}</span>
        </nav>

        <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_21rem] lg:items-start lg:gap-6">
            <div class="space-y-5">
                <article class="crs-card overflow-hidden">
                    <div class="border-b border-slate-200 bg-gradient-to-br from-slate-50 to-white px-5 py-5 sm:px-7 sm:py-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 ring-1 ring-inset ring-brand-600/20">
                                <x-icon :name="$isDigital ? 'document' : 'collection'" class="h-3.5 w-3.5" />
                                {{ $isDigital ? __('Digital resource') : __('Physical resource') }}
                            </span>

                            <a href="{{ route('public.resources.index', ['category_id' => $resource->category_id]) }}" class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200 transition hover:text-brand-700">
                                {{ $resource->category_name }}
                            </a>

                            <x-status-badge :status="$resource->availability_status" :label="$availabilityLabel" />
                        </div>

                        <h1 class="mt-4 font-display text-[28px] font-bold leading-tight tracking-tight text-slate-900 sm:text-[34px]">{{ $resource->title }}</h1>

                        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-[13px] text-slate-500">
                            <span class="inline-flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-[11px] font-semibold text-slate-600">{{ $ownerInitial }}</span>
                                {{ __('Shared by :name', ['name' => $resource->owner_name]) }}
                            </span>

                            @if ($createdAt)
                                <span class="inline-flex items-center gap-1.5">
                                    <x-icon name="calendar" class="h-4 w-4 text-slate-400" />
                                    {{ __('Added :date', ['date' => $createdAt]) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="px-5 py-6 sm:px-8 sm:py-8">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">{{ __('About this resource') }}</h2>
                        <div class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600">
                            {{ $resource->description ?: __('No description has been provided for this resource.') }}
                        </div>
                    </div>
                </article>

                @if ($isDigital)
                    <section class="crs-card overflow-hidden" aria-labelledby="resource-files-title">
                        <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-8">
                            <div>
                                <h2 id="resource-files-title" class="text-base font-semibold text-slate-900">{{ __('Files') }}</h2>
                                <p class="mt-0.5 text-sm text-slate-500">{{ __('Download available files for this digital resource.') }}</p>
                            </div>

                            @if ($isOwner)
                                <a href="{{ route('digital-files.create') }}" class="hidden items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 sm:inline-flex">
                                    <x-icon name="plus" class="h-3.5 w-3.5" />
                                    {{ __('Add file') }}
                                </a>
                            @endif
                        </div>

                        @if (count($files) > 0)
                            <ul class="divide-y divide-slate-100">
                                @foreach ($files as $file)
                                    <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 ring-1 ring-inset ring-sky-100">
                                                <x-icon name="document" class="h-5 w-5" />
                                            </span>

                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-medium text-slate-900">{{ $file->file_name }}</p>
                                                <p class="mt-0.5 text-xs text-slate-500">
                                                    {{ strtoupper((string) $file->file_type) ?: __('File') }}
                                                    <span class="mx-1 text-slate-300">·</span>
                                                    <x-file-size :bytes="$file->size" />
                                                    <span class="mx-1 text-slate-300">·</span>
                                                    {{ trans_choice(':count download|:count downloads', (int) $file->download_count, ['count' => (int) $file->download_count]) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="shrink-0 sm:ms-4">
                                            @if ($canDownload)
                                                <a href="{{ route('public.resources.download', ['resource' => $resource->id, 'digitalFile' => $file->id]) }}" class="crs-btn crs-btn-primary w-full sm:w-auto">
                                                    <x-icon name="download" class="h-4 w-4" />
                                                    {{ __('Download') }}
                                                </a>
                                            @elseif (! $viewer)
                                                <a href="{{ route('login') }}" class="crs-btn crs-btn-secondary w-full sm:w-auto">
                                                    {{ __('Sign in to download') }}
                                                </a>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-2 text-xs font-medium text-slate-500">
                                                    <x-icon name="shield-check" class="h-4 w-4" />
                                                    {{ __('Currently unavailable') }}
                                                </span>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="px-5 py-10 text-center sm:px-8">
                                <x-icon name="document" class="mx-auto h-7 w-7 text-slate-400" />
                                <p class="mt-3 text-sm font-semibold text-slate-900">{{ __('No files attached yet') }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $isOwner ? __('Add a file from the digital files panel to make it available here.') : __('The owner has not uploaded any files for this resource yet.') }}</p>
                            </div>
                        @endif
                    </section>
                @endif

                @if (! $isDigital)
                    <section class="crs-card p-5 sm:p-6" aria-labelledby="borrowing-flow-title">
                        <h2 id="borrowing-flow-title" class="text-base font-semibold text-slate-900">{{ __('How borrowing works') }}</h2>

                        <ol class="mt-5 grid gap-4 sm:grid-cols-3">
                            <li class="rounded-xl bg-slate-50 p-4">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-xs font-bold text-brand-600 shadow-sm ring-1 ring-inset ring-slate-200">1</span>
                                <p class="mt-3 text-sm font-semibold text-slate-900">{{ __('Request') }}</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Send a message and preferred dates to the owner.') }}</p>
                            </li>
                            <li class="rounded-xl bg-slate-50 p-4">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-xs font-bold text-brand-600 shadow-sm ring-1 ring-inset ring-slate-200">2</span>
                                <p class="mt-3 text-sm font-semibold text-slate-900">{{ __('Approval') }}</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('The owner approves and you arrange the pickup.') }}</p>
                            </li>
                            <li class="rounded-xl bg-slate-50 p-4">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-xs font-bold text-brand-600 shadow-sm ring-1 ring-inset ring-slate-200">3</span>
                                <p class="mt-3 text-sm font-semibold text-slate-900">{{ __('Return') }}</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Return it by the agreed date so it can be shared again.') }}</p>
                            </li>
                        </ol>
                    </section>
                @endif
            </div>

            <aside class="space-y-4 lg:sticky lg:top-[88px]">
                <section class="crs-card overflow-hidden" aria-labelledby="resource-action-title">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h2 id="resource-action-title" class="text-base font-semibold text-slate-900">{{ $isDigital ? __('Get this resource') : __('Borrow this resource') }}</h2>
                    </div>

                    <div class="p-5">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm text-slate-500">{{ __('Status') }}</span>
                            <x-status-badge :status="$resource->availability_status" :label="$availabilityLabel" />
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3">
                            <span class="text-sm text-slate-500">{{ __('Type') }}</span>
                            <span class="text-sm font-medium text-slate-900">{{ $isDigital ? __('Digital') : __('Physical') }}</span>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3">
                            <span class="text-sm text-slate-500">{{ __('Owner') }}</span>
                            <span class="min-w-0 truncate text-sm font-medium text-slate-900">{{ $resource->owner_name }}</span>
                        </div>

                        <div class="mt-5 border-t border-slate-100 pt-5">
                            @if ($isOwner)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-sm font-semibold text-slate-900">{{ __('You own this resource') }}</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Review incoming requests and manage the listing from your account.') }}</p>
                                </div>

                                <div class="mt-3 space-y-2">
                                    @if (! $isDigital)
                                        <a href="{{ route('public.owner-requests.index', ['resource_id' => $resource->id]) }}" class="crs-btn crs-btn-primary w-full">
                                            <x-icon name="inbox" class="h-4 w-4" />
                                            {{ $incomingPending > 0 ? trans_choice(':count pending request|:count pending requests', $incomingPending, ['count' => $incomingPending]) : __('View incoming requests') }}
                                        </a>
                                    @endif

                                    <a href="{{ route('resources.edit', $resource->id) }}" class="crs-btn crs-btn-secondary w-full">
                                        {{ __('Manage listing') }}
                                    </a>
                                </div>
                            @elseif ($activeRequest)
                                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold text-amber-900">{{ __('You have an open request') }}</p>
                                        <x-borrowing-status :status="$activeRequest->status" />
                                    </div>
                                    <p class="mt-1 text-xs leading-5 text-amber-700">{{ __('Track updates and request details from your account.') }}</p>
                                </div>

                                <a href="{{ route('public.borrowings.index') }}" class="mt-3 crs-btn crs-btn-secondary w-full">
                                    {{ __('View my requests') }}
                                    <x-icon name="arrow-right" class="h-4 w-4" />
                                </a>
                            @elseif ($canRequest)
                                <a href="{{ route('public.borrowings.request', $resource->id) }}" class="crs-btn crs-btn-primary w-full">
                                    <x-icon name="send" class="h-4 w-4" />
                                    {{ __('Request to borrow') }}
                                </a>
                                <p class="mt-2 text-center text-xs text-slate-500">{{ __('The owner will review your request before pickup.') }}</p>
                            @elseif (! $viewer)
                                <a href="{{ route('login') }}" class="crs-btn crs-btn-primary w-full">
                                    {{ __('Sign in to continue') }}
                                </a>
                                <p class="mt-2 text-center text-xs text-slate-500">{{ __('Use your student account to request or download resources.') }}</p>
                            @elseif (! $viewerIsActive)
                                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                                    {{ __('Your account is not active. Contact an administrator for help.') }}
                                </div>
                            @elseif ($isAdmin)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                                    {{ __('Admin accounts can review and manage resources but cannot place student borrowing requests.') }}
                                </div>
                            @elseif ($isDigital)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                                    {{ __('This digital resource is not currently available for download.') }}
                                </div>
                            @else
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-sm font-semibold text-slate-900">{{ __('Currently unavailable') }}</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('This resource is not accepting new requests right now. Browse similar resources while you wait.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="crs-card p-5">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100">
                            <x-icon name="shield-check" class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ __('Safe sharing') }}</p>
                            <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('Downloads and borrowing require an active account. Arrange handoffs in a public campus location.') }}</p>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-public-layout>

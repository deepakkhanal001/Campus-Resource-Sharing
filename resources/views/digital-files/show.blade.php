@php
    $isAdmin = auth()->user()->hasRole('admin');
    $canManage = $isAdmin || $digitalFile->resource?->owner_id === auth()->id();
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="$digitalFile->file_name"
            :description="__('Digital file attached to a resource.')"
        >
            <x-slot name="actions">
                <x-button-link :href="route('digital-files.index')" variant="secondary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    {{ __('Back') }}
                </x-button-link>

                <x-button-link :href="route('digital-files.download', $digitalFile)">
                    <x-icon name="download" class="h-4 w-4" />
                    {{ __('Download') }}
                </x-button-link>

                @if ($canManage)
                    <x-button-link :href="route('digital-files.edit', $digitalFile)" variant="secondary">
                        <x-icon name="pencil" class="h-4 w-4" />
                        {{ __('Edit') }}
                    </x-button-link>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <div class="grid gap-6 lg:grid-cols-3">
                <section class="crs-card lg:col-span-2" aria-labelledby="file-details-title">
                    <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                        <h2 id="file-details-title" class="text-base font-semibold text-slate-900">{{ __('File details') }}</h2>
                    </div>

                    <div class="flex items-start gap-4 p-5 sm:p-6">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-inset ring-brand-100">
                            <x-icon name="document" class="h-7 w-7" />
                        </span>

                        <div class="min-w-0">
                            <p class="break-words text-lg font-semibold text-slate-900">{{ $digitalFile->file_name }}</p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $digitalFile->file_type }}
                                <span class="mx-1 text-slate-300">·</span>
                                <x-file-size :bytes="$digitalFile->size" />
                            </p>
                        </div>
                    </div>

                    <dl class="grid gap-6 border-t border-slate-200 p-5 sm:grid-cols-2 sm:p-6">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Resource') }}</dt>
                            <dd class="mt-1.5 text-sm text-slate-900">
                                @if ($digitalFile->resource)
                                    <a href="{{ route('resources.show', $digitalFile->resource) }}" class="crs-link">
                                        {{ $digitalFile->resource->title }}
                                    </a>
                                @else
                                    <span class="text-slate-400">{{ __('Resource unavailable') }}</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Uploaded') }}</dt>
                            <dd class="mt-1.5 text-sm text-slate-900">{{ $digitalFile->created_at?->format('d M Y, h:i A') ?: '—' }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('File type') }}</dt>
                            <dd class="mt-1.5 text-sm text-slate-900">{{ $digitalFile->file_type }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Size') }}</dt>
                            <dd class="mt-1.5 text-sm text-slate-900"><x-file-size :bytes="$digitalFile->size" /></dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Downloads') }}</dt>
                            <dd class="mt-1.5 text-sm text-slate-900">{{ number_format((int) $digitalFile->download_count) }}</dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Storage path') }}</dt>
                            <dd class="mt-1.5 break-all rounded-lg bg-slate-50 px-3 py-2 font-mono text-xs text-slate-600">{{ $digitalFile->file_path }}</dd>
                        </div>
                    </dl>
                </section>

                <aside class="space-y-6">
                    <section class="crs-card p-5 sm:p-6" aria-label="{{ __('File actions') }}">
                        <h2 class="text-base font-semibold text-slate-900">{{ __('Actions') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Download or open the attached resource.') }}</p>

                        <div class="mt-4 space-y-2">
                            <x-button-link :href="route('digital-files.download', $digitalFile)" class="w-full">
                                <x-icon name="download" class="h-4 w-4" />
                                {{ __('Download file') }}
                            </x-button-link>

                            @if ($digitalFile->resource)
                                <x-button-link :href="route('resources.show', $digitalFile->resource)" variant="secondary" class="w-full">
                                    <x-icon name="collection" class="h-4 w-4" />
                                    {{ __('View resource') }}
                                </x-button-link>
                            @endif
                        </div>
                    </section>

                    @if ($canManage)
                        <section class="crs-card p-5 sm:p-6" aria-labelledby="delete-file-title">
                            <h2 id="delete-file-title" class="text-base font-semibold text-slate-900">{{ __('Delete file') }}</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-500">{{ __('This removes the file from the resource permanently.') }}</p>

                            <form method="POST" action="{{ route('digital-files.destroy', $digitalFile) }}" class="mt-4" data-confirm="This file will be permanently deleted from the resource." data-confirm-title="Delete file?" data-confirm-label="Delete file">
                                @csrf
                                @method('DELETE')

                                <x-danger-button class="w-full">
                                    <x-icon name="trash" class="h-4 w-4" />
                                    {{ __('Delete file') }}
                                </x-danger-button>
                            </form>
                        </section>
                    @endif
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>

@php
    $ownerInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($resource->owner_name ?? 'U', 0, 1));
@endphp

<x-public-layout :title="__('Request :title', ['title' => $resource->title])">
    <div class="mx-auto max-w-5xl px-4 py-7 sm:px-6 lg:px-8 lg:py-8">
        <nav class="flex flex-wrap items-center gap-1.5 text-xs text-slate-500" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('public.home') }}" class="transition hover:text-slate-900">{{ __('Home') }}</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('public.resources.index') }}" class="transition hover:text-slate-900">{{ __('Browse') }}</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('public.resources.show', $resource->id) }}" class="max-w-[14rem] truncate transition hover:text-slate-900">{{ $resource->title }}</a>
            <span aria-hidden="true">/</span>
            <span class="font-medium text-slate-700">{{ __('Request') }}</span>
        </nav>

        <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_19rem] lg:items-start lg:gap-6">
            <section class="crs-card overflow-hidden">
                <div class="border-b border-slate-200 px-5 py-5 sm:px-7">
                    <h1 class="font-display text-[26px] font-bold tracking-tight text-slate-900">{{ __('Request to borrow') }}</h1>
                    <p class="mt-2 text-[13px] leading-5 text-slate-500">{{ __('Tell the owner when you would like to pick this up. They will review your request and confirm the details.') }}</p>
                </div>

                <form method="POST" action="{{ route('public.borrowings.store', $resource->id) }}" class="p-5 sm:p-7">
                    @csrf

                    <div class="space-y-5">
                        <div>
                            <x-input-label for="message" :value="__('Message to the owner')" />
                            <textarea
                                id="message"
                                name="message"
                                rows="5"
                                class="crs-textarea mt-1.5"
                                placeholder="{{ __('Introduce yourself and mention when or where you can collect the resource.') }}"
                            >{{ old('message') }}</textarea>
                            <p class="crs-help">{{ __('Optional. Keep it short and relevant.') }}</p>
                            <x-input-error :messages="$errors->get('message')" class="mt-2" />
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <x-input-label for="pickup_date" :value="__('Preferred pickup date')" />
                                <x-text-input
                                    id="pickup_date"
                                    name="pickup_date"
                                    type="date"
                                    class="mt-1.5"
                                    min="{{ $minimumDate }}"
                                    :value="old('pickup_date')"
                                />
                                <p class="crs-help">{{ __('Optional. The owner will confirm the final time.') }}</p>
                                <x-input-error :messages="$errors->get('pickup_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="return_date" :value="__('Expected return date')" />
                                <x-text-input
                                    id="return_date"
                                    name="return_date"
                                    type="date"
                                    class="mt-1.5"
                                    min="{{ $minimumDate }}"
                                    :value="old('return_date')"
                                />
                                <p class="crs-help">{{ __('Optional. Must be on or after the pickup date.') }}</p>
                                <x-input-error :messages="$errors->get('return_date')" class="mt-2" />
                            </div>
                        </div>

                        @if ($errors->get('request') || $errors->get('cancel'))
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                                {{ $errors->first('request') ?: $errors->first('cancel') }}
                            </div>
                        @endif

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start gap-3">
                                <x-icon name="information-circle" class="mt-0.5 h-4 w-4 shrink-0 text-slate-500" />
                                <p class="text-xs leading-5 text-slate-600">{{ __('A request does not reserve the resource until the owner approves it. You can cancel while the request is still pending.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('public.resources.show', $resource->id) }}" class="crs-btn crs-btn-ghost">
                            <x-icon name="arrow-left" class="h-4 w-4" />
                            {{ __('Back to resource') }}
                        </a>

                        <x-primary-button>
                            <x-icon name="send" class="h-4 w-4" />
                            {{ __('Send request') }}
                        </x-primary-button>
                    </div>
                </form>
            </section>

            <aside class="space-y-4 lg:sticky lg:top-[88px]">
                <section class="crs-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('You are requesting') }}</p>

                    <div class="mt-3 flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-inset ring-brand-100">
                            <x-icon name="collection" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold leading-5 text-slate-900">{{ $resource->title }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $resource->category_name }}</p>
                        </div>
                    </div>

                    <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">{{ __('Owner') }}</dt>
                            <dd class="flex min-w-0 items-center gap-2 font-medium text-slate-900">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-semibold text-slate-600">{{ $ownerInitial }}</span>
                                <span class="truncate">{{ $resource->owner_name }}</span>
                            </dd>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">{{ __('Availability') }}</dt>
                            <dd><x-status-badge status="available" :label="__('Available')" /></dd>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">{{ __('Resource type') }}</dt>
                            <dd class="font-medium text-slate-900">{{ __('Physical') }}</dd>
                        </div>
                    </dl>
                </section>

                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                    <div class="flex items-start gap-3">
                        <x-icon name="shield-check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
                        <div>
                            <p class="text-sm font-semibold text-emerald-900">{{ __('A respectful handoff') }}</p>
                            <p class="mt-1 text-xs leading-5 text-emerald-700">{{ __('Agree on a public campus location, inspect the item together, and return it in the same condition.') }}</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-public-layout>

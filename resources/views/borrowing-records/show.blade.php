@php
    $canManage = (int) $borrowingRecord->owner_id === (int) auth()->id()
        && (int) $borrowingRecord->requester_id !== (int) auth()->id();
    $isOverdue = in_array($borrowingRecord->status, ['approved', 'picked_up'], true)
        && $borrowingRecord->return_date
        && $borrowingRecord->return_date->lt(today());
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Borrowing request #:id', ['id' => $borrowingRecord->id])"
            :description="$borrowingRecord->resource?->title ?: __('Resource unavailable')"
        >
            <x-slot name="actions">
                <x-button-link :href="route('borrowing-records.index')" variant="secondary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    {{ __('Back') }}
                </x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <section class="crs-card" aria-labelledby="borrowing-overview-title">
                        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                            <h2 id="borrowing-overview-title" class="text-base font-semibold text-slate-900">{{ __('Request overview') }}</h2>
                        </div>

                        <dl class="grid gap-6 p-5 sm:grid-cols-2 sm:p-6">
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Resource') }}</dt>
                                <dd class="mt-1.5 text-sm font-medium text-slate-900">
                                    @if ($borrowingRecord->resource)
                                        <a href="{{ route('resources.show', $borrowingRecord->resource) }}" class="crs-link">{{ $borrowingRecord->resource->title }}</a>
                                    @else
                                        <span class="text-slate-400">{{ __('Resource unavailable') }}</span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Requester') }}</dt>
                                <dd class="mt-1.5 text-sm text-slate-900">{{ $borrowingRecord->requester?->name ?: '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Owner') }}</dt>
                                <dd class="mt-1.5 text-sm text-slate-900">{{ $borrowingRecord->owner?->name ?: '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Pickup date') }}</dt>
                                <dd class="mt-1.5 text-sm text-slate-900">{{ $borrowingRecord->pickup_date?->format('d M Y') ?: __('Not set') }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Return date') }}</dt>
                                <dd class="mt-1.5 text-sm {{ $isOverdue ? 'font-semibold text-rose-600' : 'text-slate-900' }}">
                                    {{ $borrowingRecord->return_date?->format('d M Y') ?: __('Not set') }}
                                </dd>
                            </div>
                        </dl>

                        @if ($borrowingRecord->request_message || $borrowingRecord->rejection_reason)
                            <div class="space-y-4 border-t border-slate-200 px-5 py-4 sm:px-6">
                                @if ($borrowingRecord->request_message)
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Requester message') }}</p>
                                        <p class="mt-1.5 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $borrowingRecord->request_message }}</p>
                                    </div>
                                @endif

                                @if ($borrowingRecord->rejection_reason)
                                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-rose-700">{{ __('Rejection remarks') }}</p>
                                        <p class="mt-1.5 whitespace-pre-line text-sm leading-6 text-rose-700">{{ $borrowingRecord->rejection_reason }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </section>

                    <section class="crs-card" aria-labelledby="borrowing-timeline-title">
                        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                            <h2 id="borrowing-timeline-title" class="text-base font-semibold text-slate-900">{{ __('Timeline') }}</h2>
                        </div>

                        <div class="p-5 sm:p-6">
                            <ol class="relative space-y-5 border-s border-slate-200 ps-5">
                                <li class="relative">
                                    <span class="absolute -start-[1.65rem] top-1 h-2.5 w-2.5 rounded-full border-2 border-white bg-brand-500 ring-1 ring-brand-200"></span>
                                    <p class="text-sm font-medium text-slate-900">{{ __('Request created') }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $borrowingRecord->created_at?->format('d M Y, h:i A') ?: '—' }}</p>
                                </li>

                                <li class="relative">
                                    <span class="absolute -start-[1.65rem] top-1 h-2.5 w-2.5 rounded-full border-2 border-white {{ $borrowingRecord->pickup_date ? 'bg-sky-500 ring-1 ring-sky-200' : 'bg-slate-300' }}"></span>
                                    <p class="text-sm font-medium text-slate-900">{{ __('Scheduled pickup') }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $borrowingRecord->pickup_date?->format('d M Y') ?: __('Not scheduled') }}</p>
                                </li>

                                <li class="relative">
                                    <span class="absolute -start-[1.65rem] top-1 h-2.5 w-2.5 rounded-full border-2 border-white {{ $isOverdue ? 'bg-rose-500 ring-1 ring-rose-200' : ($borrowingRecord->return_date ? 'bg-amber-500 ring-1 ring-amber-200' : 'bg-slate-300') }}"></span>
                                    <p class="text-sm font-medium text-slate-900">{{ __('Expected return') }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $borrowingRecord->return_date?->format('d M Y') ?: __('Not scheduled') }}
                                        @if ($isOverdue)
                                            <span class="ms-1 font-medium text-rose-600">· {{ __('Overdue') }}</span>
                                        @endif
                                    </p>
                                </li>
                            </ol>
                        </div>
                    </section>
                </div>

                <aside class="space-y-6">
                    <section class="crs-card p-5 sm:p-6" aria-labelledby="borrowing-status-title">
                        <h2 id="borrowing-status-title" class="text-base font-semibold text-slate-900">{{ __('Status') }}</h2>

                        <div class="mt-3">
                            @if ($isOverdue)
                                <x-status-badge status="overdue" />
                            @else
                                <x-status-badge :status="$borrowingRecord->status" />
                            @endif
                        </div>

                        @if ($isOverdue)
                            <div class="mt-4 flex items-start gap-2 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                                <x-icon name="exclamation-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
                                <p>{{ __('This loan is past its recorded return date.') }}</p>
                            </div>
                        @endif

                        <dl class="mt-4 space-y-3 border-t border-slate-100 pt-4 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">{{ __('Last updated') }}</dt>
                                <dd class="text-right font-medium text-slate-900">{{ $borrowingRecord->updated_at?->format('d M Y') ?: '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-slate-500">{{ __('Record ID') }}</dt>
                                <dd class="font-medium text-slate-900">#{{ $borrowingRecord->id }}</dd>
                            </div>
                        </dl>
                    </section>

                    @if ($canManage)
                        <section class="crs-card p-5 sm:p-6" aria-labelledby="borrowing-actions-title">
                            <h2 id="borrowing-actions-title" class="text-base font-semibold text-slate-900">{{ __('Request actions') }}</h2>

                            @error('action')
                                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="mt-4 space-y-4">
                                @if ($borrowingRecord->status === 'pending')
                                    <form method="POST" action="{{ route('borrowing-records.approve', $borrowingRecord) }}" data-confirm="Approving marks the resource as borrowed and blocks new requests until it is returned." data-confirm-title="Approve request?" data-confirm-label="Approve request">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                                            <x-icon name="check" class="h-4 w-4" />
                                            {{ __('Approve request') }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('borrowing-records.reject', $borrowingRecord) }}" class="space-y-3 border-t border-slate-200 pt-4" data-confirm="The requester will be notified and the resource will remain available." data-confirm-title="Reject request?" data-confirm-label="Reject request">
                                        @csrf
                                        @method('PATCH')

                                        <div>
                                            <x-input-label for="remarks" :value="__('Remarks')" />
                                            <textarea id="remarks" name="remarks" rows="3" maxlength="1000" class="crs-textarea mt-1.5" placeholder="{{ __('Optional reason for rejecting this request.') }}">{{ old('remarks') }}</textarea>
                                            <p class="crs-help">{{ __('Optional. The requester will see these remarks.') }}</p>
                                            <x-input-error :messages="$errors->get('remarks')" class="mt-2" />
                                        </div>

                                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                                            <x-icon name="x-mark" class="h-4 w-4" />
                                            {{ __('Reject request') }}
                                        </button>
                                    </form>
                                @elseif ($borrowingRecord->status === 'approved')
                                    <form method="POST" action="{{ route('borrowing-records.picked-up', $borrowingRecord) }}" data-confirm="Confirm that the requester has collected this resource." data-confirm-title="Mark as taken?" data-confirm-label="Mark as taken">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                                            <x-icon name="check-circle" class="h-4 w-4" />
                                            {{ __('Mark as taken') }}
                                        </button>
                                    </form>
                                @elseif ($borrowingRecord->status === 'picked_up')
                                    <form method="POST" action="{{ route('borrowing-records.returned', $borrowingRecord) }}" data-confirm="Confirm that this resource has been returned. It will become available for new requests." data-confirm-title="Mark as returned?" data-confirm-label="Mark as returned">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                            <x-icon name="arrow-path" class="h-4 w-4" />
                                            {{ __('Mark as returned') }}
                                        </button>
                                    </form>
                                @else
                                    <p class="text-sm text-slate-500">{{ __('This request is closed and cannot be changed.') }}</p>
                                @endif
                            </div>
                        </section>
                    @endif
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>

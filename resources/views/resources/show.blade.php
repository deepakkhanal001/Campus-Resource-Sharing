@php
    $isAdmin = auth()->user()->hasRole('admin');
    $canManage = $isAdmin || (int) $resource->owner_id === (int) auth()->id();
    $listedAt = $resource->created_at
        ? \Illuminate\Support\Carbon::parse($resource->created_at)->format('d M Y')
        : null;
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="$resource->title"
            :description="ucfirst($resource->type).' · '.($resource->category_name ?: __('Uncategorised'))"
        >
            <x-slot name="actions">
                <x-button-link :href="route('resources.index')" variant="secondary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    {{ __('Back') }}
                </x-button-link>

                <x-button-link :href="route('public.resources.show', $resource->id)" variant="secondary">
                    <x-icon name="collection" class="h-4 w-4" />
                    {{ __('View public page') }}
                </x-button-link>

                @if ($canManage)
                    <x-button-link :href="route('resources.edit', $resource->id)">
                        <x-icon name="pencil" class="h-4 w-4" />
                        {{ __('Edit') }}
                    </x-button-link>
                @endif
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-[1440px] space-y-6 px-4 sm:px-6 lg:px-8 2xl:max-w-screen-2xl">
            @include('partials.flash')

            <div class="grid gap-6 xl:grid-cols-3">
                <div class="space-y-6 xl:col-span-2">
                    <section class="crs-card" aria-labelledby="resource-overview-title">
                        <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div>
                                <h2 id="resource-overview-title" class="text-base font-semibold text-slate-900">{{ __('Resource overview') }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ __('Description and identifying details.') }}</p>
                            </div>

                            <x-status-badge :status="$resource->availability_status" class="shrink-0 self-start sm:self-auto" />
                        </div>

                        <div class="p-5 sm:p-6">
                            <p class="whitespace-pre-line text-sm leading-7 text-slate-700">{{ $resource->description ?: __('No description has been provided for this resource.') }}</p>
                        </div>
                    </section>

                    @if ($resource->type === 'digital')
                        <section class="crs-card" aria-labelledby="digital-files-title">
                            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                <div>
                                    <h2 id="digital-files-title" class="text-base font-semibold text-slate-900">{{ __('Digital files') }}</h2>
                                    <p class="mt-1 text-sm text-slate-500">{{ __('Files attached to this digital resource.') }}</p>
                                </div>

                                @if ($canManage)
                                    <x-button-link :href="route('digital-files.create', ['resource_id' => $resource->id])" variant="secondary">
                                        <x-icon name="upload" class="h-4 w-4" />
                                        {{ __('Add file') }}
                                    </x-button-link>
                                @endif
                            </div>

                            @if (count($digitalFiles) === 0)
                                <x-empty-state
                                    icon="document"
                                    :title="__('No files uploaded')"
                                    :description="__('Upload a file to make this digital resource available to others.')"
                                >
                                    @if ($canManage)
                                        <x-slot name="action">
                                            <x-button-link :href="route('digital-files.create', ['resource_id' => $resource->id])" variant="secondary">
                                                {{ __('Upload a file') }}
                                            </x-button-link>
                                        </x-slot>
                                    @endif
                                </x-empty-state>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="crs-table">
                                        <thead class="crs-thead">
                                            <tr>
                                                <th scope="col" class="crs-th">{{ __('File') }}</th>
                                                <th scope="col" class="crs-th hidden sm:table-cell">{{ __('Type') }}</th>
                                                <th scope="col" class="crs-th hidden md:table-cell">{{ __('Size') }}</th>
                                                <th scope="col" class="crs-th text-right">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>

                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            @foreach ($digitalFiles as $digitalFile)
                                                <tr class="transition hover:bg-slate-50/70">
                                                    <td class="crs-td">
                                                        <div class="flex min-w-0 items-center gap-3">
                                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                                                <x-icon name="document" class="h-4 w-4" />
                                                            </span>
                                                            <span class="min-w-0 break-words font-medium text-slate-900">{{ $digitalFile->file_name }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="crs-td hidden sm:table-cell">{{ $digitalFile->file_type ?: '—' }}</td>
                                                    <td class="crs-td hidden whitespace-nowrap md:table-cell"><x-file-size :bytes="$digitalFile->size" /></td>
                                                    <td class="crs-td whitespace-nowrap text-right">
                                                        <a href="{{ route('digital-files.show', $digitalFile->id) }}" class="crs-link">{{ __('View') }}</a>
                                                        <a href="{{ route('digital-files.download', $digitalFile->id) }}" class="crs-link ms-3">{{ __('Download') }}</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </section>
                    @endif

                    <section class="crs-card" aria-labelledby="resource-borrowings-title">
                        <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div>
                                <h2 id="resource-borrowings-title" class="text-base font-semibold text-slate-900">{{ __('Borrowing records') }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ __('Requests and loans associated with this resource.') }}</p>
                            </div>

                            <span class="text-sm text-slate-500">{{ number_format(count($borrowingRecords)) }} {{ __('total') }}</span>
                        </div>

                        @if (count($borrowingRecords) === 0)
                            <x-empty-state
                                icon="borrowing"
                                :title="__('No borrowing records')"
                                :description="__('Requests for this resource will appear here once someone asks to borrow it.')"
                            />
                        @else
                            <div class="overflow-x-auto">
                                <table class="crs-table">
                                    <thead class="crs-thead">
                                        <tr>
                                            <th scope="col" class="crs-th">{{ __('Requester') }}</th>
                                            <th scope="col" class="crs-th">{{ __('Status') }}</th>
                                            <th scope="col" class="crs-th hidden sm:table-cell">{{ __('Pickup') }}</th>
                                            <th scope="col" class="crs-th hidden md:table-cell">{{ __('Return') }}</th>
                                            <th scope="col" class="crs-th text-right">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach ($borrowingRecords as $borrowingRecord)
                                            @php
                                                $pickupDate = $borrowingRecord->pickup_date
                                                    ? \Illuminate\Support\Carbon::parse($borrowingRecord->pickup_date)->format('d M Y')
                                                    : null;
                                                $returnDate = $borrowingRecord->return_date
                                                    ? \Illuminate\Support\Carbon::parse($borrowingRecord->return_date)->format('d M Y')
                                                    : null;
                                            @endphp

                                            <tr class="transition hover:bg-slate-50/70">
                                                <td class="crs-td">{{ $borrowingRecord->requester_name ?: '—' }}</td>
                                                <td class="crs-td"><x-borrowing-status :status="$borrowingRecord->status" /></td>
                                                <td class="crs-td hidden whitespace-nowrap sm:table-cell">{{ $pickupDate ?: '—' }}</td>
                                                <td class="crs-td hidden whitespace-nowrap md:table-cell">{{ $returnDate ?: '—' }}</td>
                                                <td class="crs-td text-right">
                                                    <a href="{{ route('borrowing-records.show', $borrowingRecord->id) }}" class="crs-link">{{ __('View') }}</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </section>
                </div>

                <aside class="space-y-6">
                    <section class="crs-card" aria-labelledby="resource-summary-title">
                        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                            <h2 id="resource-summary-title" class="text-base font-semibold text-slate-900">{{ __('Summary') }}</h2>
                        </div>

                        <dl class="divide-y divide-slate-100">
                            <div class="flex items-center justify-between gap-4 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-slate-500">{{ __('Type') }}</dt>
                                <dd class="text-sm font-medium text-slate-900">{{ ucfirst($resource->type) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-slate-500">{{ __('Category') }}</dt>
                                <dd class="text-right text-sm font-medium text-slate-900">{{ $resource->category_name ?: __('Uncategorised') }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-slate-500">{{ __('Owner') }}</dt>
                                <dd class="text-right text-sm font-medium text-slate-900">{{ $resource->owner_name ?: '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-slate-500">{{ __('Availability') }}</dt>
                                <dd><x-status-badge :status="$resource->availability_status" /></dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-slate-500">{{ __('Listed') }}</dt>
                                <dd class="text-right text-sm font-medium text-slate-900">{{ $listedAt ?: '—' }}</dd>
                            </div>
                        </dl>
                    </section>

                    @if ($canManage)
                        <section class="crs-card" aria-labelledby="resource-management-title">
                            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                                <h2 id="resource-management-title" class="text-base font-semibold text-slate-900">{{ __('Manage resource') }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ __('Update the listing or remove it from the library.') }}</p>
                            </div>

                            <div class="space-y-2 p-4 sm:p-5">
                                <x-button-link :href="route('resources.edit', $resource->id)" variant="secondary" class="w-full">
                                    <x-icon name="pencil" class="h-4 w-4" />
                                    {{ __('Edit resource') }}
                                </x-button-link>

                                <form method="POST" action="{{ route('resources.destroy', $resource->id) }}" data-confirm="This resource and its related records will be removed from the library." data-confirm-title="Delete resource?" data-confirm-label="Delete resource">
                                    @csrf
                                    @method('DELETE')

                                    <x-danger-button class="w-full">
                                        <x-icon name="trash" class="h-4 w-4" />
                                        {{ __('Delete resource') }}
                                    </x-danger-button>
                                </form>
                            </div>
                        </section>
                    @endif
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>

@php
    $isAdmin = auth()->user()->hasRole('admin');
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="$category->name"
            :description="__('Category details and the resources filed under it.')"
        >
            <x-slot name="actions">
                <x-button-link :href="route('categories.index')" variant="secondary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    {{ __('Back') }}
                </x-button-link>

                @if ($isAdmin)
                    <x-button-link :href="route('categories.edit', $category)">
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

            <div class="grid gap-6 lg:grid-cols-3">
                <section class="crs-card lg:col-span-2" aria-labelledby="category-details-title">
                    <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                        <h2 id="category-details-title" class="text-base font-semibold text-slate-900">{{ __('Category details') }}</h2>
                    </div>

                    <dl class="grid gap-6 p-5 sm:grid-cols-2 sm:p-6">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Name') }}</dt>
                            <dd class="mt-1.5 break-words text-sm font-medium text-slate-900">{{ $category->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Created') }}</dt>
                            <dd class="mt-1.5 text-sm text-slate-900">{{ $category->created_at?->format('d M Y, h:i A') ?: '—' }}</dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Description') }}</dt>
                            <dd class="mt-1.5 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $category->description ?: __('No description provided.') }}</dd>
                        </div>
                    </dl>
                </section>

                <aside class="crs-card p-5 sm:p-6" aria-label="{{ __('Category summary') }}">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 text-brand-600 ring-1 ring-inset ring-brand-100">
                            <x-icon name="collection" class="h-5 w-5" />
                        </span>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Resources') }}</p>
                            <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">{{ number_format($category->resources->count()) }}</p>
                        </div>
                    </div>

                    <p class="mt-4 text-sm leading-6 text-slate-500">
                        {{ __('Resources assigned to this category appear below. Open a resource to review its details, availability, and requests.') }}
                    </p>

                    @if ($isAdmin)
                        <div class="mt-5 border-t border-slate-100 pt-5">
                            <form method="POST" action="{{ route('categories.destroy', $category) }}" data-confirm="This category will be permanently deleted. Categories that still contain resources cannot be removed." data-confirm-title="Delete category?" data-confirm-label="Delete category">
                                @csrf
                                @method('DELETE')
                                <x-danger-button>
                                    <x-icon name="trash" class="h-4 w-4" />
                                    {{ __('Delete category') }}
                                </x-danger-button>
                            </form>
                        </div>
                    @endif
                </aside>
            </div>

            <section class="crs-card" aria-labelledby="category-resources-title">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 id="category-resources-title" class="text-base font-semibold text-slate-900">{{ __('Resources in this category') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('A quick view of the items filed here.') }}</p>
                    </div>

                    <span class="text-sm text-slate-500">{{ number_format($category->resources->count()) }} {{ __('total') }}</span>
                </div>

                @if ($category->resources->isEmpty())
                    <x-empty-state
                        icon="collection"
                        :title="__('No resources in this category')"
                        :description="__('Resources assigned to this category will appear here.')"
                    >
                        <x-slot name="action">
                            <x-button-link :href="route('resources.create')" variant="secondary">
                                {{ __('Add a resource') }}
                            </x-button-link>
                        </x-slot>
                    </x-empty-state>
                @else
                    <div class="overflow-x-auto">
                        <table class="crs-table">
                            <thead class="crs-thead">
                                <tr>
                                    <th scope="col" class="crs-th">{{ __('Title') }}</th>
                                    <th scope="col" class="crs-th hidden sm:table-cell">{{ __('Owner') }}</th>
                                    <th scope="col" class="crs-th">{{ __('Status') }}</th>
                                    <th scope="col" class="crs-th text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($category->resources as $resource)
                                    <tr class="transition hover:bg-slate-50/70">
                                        <td class="crs-td">
                                            <a href="{{ route('resources.show', $resource) }}" class="font-semibold text-slate-900 transition hover:text-brand-600">
                                                {{ $resource->title }}
                                            </a>
                                        </td>
                                        <td class="crs-td hidden sm:table-cell">{{ $resource->owner?->name ?: '—' }}</td>
                                        <td class="crs-td"><x-status-badge :status="$resource->availability_status" /></td>
                                        <td class="crs-td text-right">
                                            <a href="{{ route('resources.show', $resource) }}" class="crs-link">{{ __('View') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>

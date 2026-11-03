<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Edit borrowing record')"
            :description="__('Update the resource, status, or scheduled dates.')"
        >
            <x-slot name="actions">
                <x-button-link :href="route('borrowing-records.show', $borrowingRecord)" variant="secondary">
                    <x-icon name="eye" class="h-4 w-4" />
                    {{ __('View record') }}
                </x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-3xl space-y-4 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <div class="crs-card">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <h2 class="text-base font-semibold text-slate-900">{{ __('Request details') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('All participants can see these updates.') }}</p>
                </div>

                <form method="POST" action="{{ route('borrowing-records.update', $borrowingRecord) }}" class="p-5 sm:p-6">
                    @method('PUT')
                    @include('borrowing-records._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

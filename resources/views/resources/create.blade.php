<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Create resource')"
            :description="__('Add an item or digital resource to the shared library.')"
        >
            <x-slot name="actions">
                <x-button-link :href="route('resources.index')" variant="secondary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    {{ __('Back to resources') }}
                </x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-3xl space-y-4 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <div class="crs-card">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <h2 class="text-base font-semibold text-slate-900">{{ __('Resource details') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Provide enough detail for others to understand what is being shared.') }}</p>
                </div>

                <form method="POST" action="{{ route('resources.store') }}" class="p-5 sm:p-6">
                    @include('resources._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

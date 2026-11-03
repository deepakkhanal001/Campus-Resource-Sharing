<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Create program')"
            :description="__('Add a program that students can select during registration.')"
        >
            <x-slot name="actions">
                <x-button-link :href="route('programs.index')" variant="secondary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    {{ __('Back to programs') }}
                </x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-3xl space-y-4 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <div class="crs-card">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <h2 class="text-base font-semibold text-slate-900">{{ __('Program details') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Give this program a unique code and a clear name.') }}</p>
                </div>

                <form method="POST" action="{{ route('programs.store') }}" class="p-5 sm:p-6">
                    @include('programs._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

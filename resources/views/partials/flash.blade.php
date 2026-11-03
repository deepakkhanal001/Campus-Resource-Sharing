@if (session('success'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="mb-4 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm"
        role="status"
    >
        <x-icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
        <p class="min-w-0 flex-1 leading-6">{{ session('success') }}</p>
        <button type="button" class="rounded p-1 text-emerald-600 transition hover:bg-emerald-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" @click="show = false" aria-label="Dismiss success message">
            <x-icon name="x-mark" class="h-4 w-4" />
        </button>
    </div>
@endif

@if (session('error'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="mb-4 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm"
        role="alert"
    >
        <x-icon name="exclamation-circle" class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" />
        <p class="min-w-0 flex-1 leading-6">{{ session('error') }}</p>
        <button type="button" class="rounded p-1 text-rose-600 transition hover:bg-rose-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500" @click="show = false" aria-label="Dismiss error message">
            <x-icon name="x-mark" class="h-4 w-4" />
        </button>
    </div>
@endif

@if ($errors->any())
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="mb-4 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm"
        role="alert"
    >
        <x-icon name="exclamation-triangle" class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" />
        <div class="min-w-0 flex-1">
            <p class="font-medium">{{ __('Please correct the following:') }}</p>
            <ul class="mt-1 list-disc space-y-1 ps-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="rounded p-1 text-rose-600 transition hover:bg-rose-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500" @click="show = false" aria-label="Dismiss validation errors">
            <x-icon name="x-mark" class="h-4 w-4" />
        </button>
    </div>
@endif

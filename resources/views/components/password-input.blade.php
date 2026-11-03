@props(['disabled' => false])

<div x-data="{ show: false }" class="relative">
    <input
        @disabled($disabled)
        {{ $attributes->except(['type'])->merge(['class' => 'crs-input pr-16', 'type' => 'password']) }}
        x-bind:type="show ? 'text' : 'password'"
    >

    <button
        type="button"
        x-on:click="show = !show"
        class="absolute inset-y-1 end-1 inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 text-[11px] font-semibold uppercase tracking-wide text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
        :aria-label="show ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
        :title="show ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
    >
        <span x-text="show ? '{{ __('Hide') }}' : '{{ __('Show') }}'"></span>
    </button>
</div>

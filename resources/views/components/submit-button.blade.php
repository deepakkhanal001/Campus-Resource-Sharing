@props(['label' => 'Save', 'loading' => 'Saving...'])

<button
    type="submit"
    x-data="{ submitting: false }"
    x-init="$el.closest('form')?.addEventListener('submit', () => setTimeout(() => submitting = true, 0))"
    x-bind:disabled="submitting"
    x-bind:aria-busy="submitting"
    {{ $attributes->merge([
        'class' => 'crs-btn crs-btn-primary',
    ]) }}
>
    <span x-show="!submitting" class="inline-flex items-center gap-2">
        <x-icon name="check" class="h-4 w-4" />
        {{ $label }}
    </span>

    <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
        <x-icon name="arrow-path" class="h-4 w-4 animate-spin" />
        {{ $loading }}
    </span>
</button>

<button {{ $attributes->merge(['type' => 'button', 'class' => 'crs-btn crs-btn-secondary']) }}>
    {{ $slot }}
</button>
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'crs-btn crs-btn-primary']) }}>
    {{ $slot }}
</button>
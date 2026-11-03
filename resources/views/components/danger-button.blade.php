<button {{ $attributes->merge(['type' => 'submit', 'class' => 'crs-btn crs-btn-danger']) }}>
    {{ $slot }}
</button>
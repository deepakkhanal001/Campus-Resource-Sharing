@props(['href', 'variant' => 'primary'])

@php
    $variants = [
        'primary' => 'crs-btn-primary',
        'secondary' => 'crs-btn-secondary',
        'danger' => 'crs-btn-danger',
        'ghost' => 'crs-btn-ghost',
    ];
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'crs-btn ' . ($variants[$variant] ?? $variants['primary']),
    ]) }}
>
    {{ $slot }}
</a>
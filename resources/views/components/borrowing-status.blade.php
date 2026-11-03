@props(['status'])

@php
    $labels = [
        'pending' => __('Requested'),
        'approved' => __('Approved'),
        'picked_up' => __('Taken'),
        'returned' => __('Returned'),
        'rejected' => __('Rejected'),
        'cancelled' => __('Cancelled'),
    ];

    $normalized = strtolower((string) $status);
    $label = $labels[$normalized] ?? ucfirst(str_replace('_', ' ', $normalized));
    $tone = $normalized === 'picked_up' ? 'picked up' : $normalized;
@endphp

<x-status-badge :status="$tone" :label="$label" {{ $attributes }} />

@props(['status', 'label' => null, 'dot' => true])

@php
    $normalized = strtolower(str_replace('_', ' ', (string) $status));

    $tone = match ($normalized) {
        'available', 'active', 'approved', 'returned', 'read', 'completed' => 'emerald',
        'pending', 'unread' => 'amber',
        'borrowed', 'picked up' => 'sky',
        'rejected', 'overdue', 'inactive', 'suspended' => 'rose',
        'admin' => 'brand',
        default => 'slate',
    };

    $classes = match ($tone) {
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'sky' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-600/20',
        default => 'bg-slate-100 text-slate-600 ring-slate-500/10',
    };

    $dotClasses = match ($tone) {
        'emerald' => 'bg-emerald-500',
        'amber' => 'bg-amber-500',
        'sky' => 'bg-sky-500',
        'rose' => 'bg-rose-500',
        'brand' => 'bg-brand-500',
        default => 'bg-slate-400',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {$classes}"]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full {{ $dotClasses }}" aria-hidden="true"></span>
    @endif

    {{ $label ?? ucfirst($normalized) }}
</span>

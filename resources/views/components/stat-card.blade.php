@props([
    'label',
    'value',
    'icon' => 'information-circle',
    'tone' => 'brand',
    'href' => null,
    'hint' => null,
])

@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-600 ring-brand-100',
        'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        'sky' => 'bg-sky-50 text-sky-600 ring-sky-100',
        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
        'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
        'slate' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];

    $toneClasses = $tones[$tone] ?? $tones['slate'];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge([
        'class' => 'crs-card group flex min-w-0 items-start justify-between gap-4 p-5 '
            .($href ? 'transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-lift focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2' : ''),
    ]) }}
>
    <div class="min-w-0">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</p>
        <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ $value }}</p>

        @if ($hint)
            <p class="mt-1 text-xs leading-5 text-slate-500">{{ $hint }}</p>
        @endif
    </div>

    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ring-1 ring-inset {{ $toneClasses }}">
        <x-icon :name="$icon" class="h-5 w-5" />
    </span>
</{{ $tag }}>
